<?php

namespace Module\System\Role;

use Illuminate\Http\Request;
use LaravelModule\Controllers\Controller as BaseController;

class Controller extends BaseController
{

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getIndex(Request $request)
    {
        return view('module::index');
    }

    public function postIndex(Request $request, Model\Role $role)
    {
        // 筛选
        if ($request->has('filterRules')) {
            $filterRules = collect(json_decode($request->input('filterRules'), true));
            $filterRules->each(function ($rule) use (&$role) {
                $field = array_get($rule, 'field');
                $value = array_get($rule, 'value');

                switch (array_get($rule, 'op')) {
                    case 'contains':
                        $role = $role->where($field, 'like', "%{$value}%");
                        break;
                    case 'notcontains':
                        $role = $role->where($field, 'not like', "%{$value}%");
                        break;
                    case 'beginwith':
                        $role = $role->where($field, 'like', "{$value}%");
                        break;
                    case 'endwith':
                        $role = $role->where($field, 'like', "{$value}%");
                        break;
                    case 'equal':
                        $role = $role->where($field, $value);
                        break;
                    case 'notequal':
                        $role = $role->where($field, '<>', $value);
                        break;
                    case 'less':
                        $role = $role->where($field, '<', $value);
                        break;
                    case 'lessorequal':
                        $role = $role->where($field, '<=', $value);
                        break;
                    case 'greater':
                        $role = $role->where($field, '>', $value);
                        break;
                    case 'greaterorequal':
                        $role = $role->where($field, '>=', $value);
                        break;
                    default:
                        $role = $role->where($field, $value);
                }
            });
        }

        // 排序
        if ($request->has('sort')) {
            $sorts = collect(explode(',', $request->input('sort', '')));
            $orders = collect(explode(',', $request->input('order', '')));
            $sorts->each(function ($sort, $index) use (&$role, $orders) {
                $role = $role->orderBy($sort, $orders->get($index));
            });
        }

        $rows = $role
            ->where('parent', $request->input('id', 0))
            ->get();

        // 获取包含子角色数据
        $children = Model\Role::whereIn('parent', $rows->pluck('id')->toArray())
            ->select('parent')
            ->get()
            ->pluck('parent')
            ->unique();

        return $rows->map(function ($row) use ($children) {
            $row->state = $children->contains($row->id) ? 'closed' : 'open';
            return $row;
        });
    }

    public function getCreate()
    {
        return view('module::create');
    }

    public function postCreate(Request $request)
    {
        Model\Role::insert([
            'parent' => $request->input('parent', 0),
            'role' => $request->input('role'),
            'name' => $request->input('name'),
            'created_at' => date('c'),
            'updated_at' => date('c'),
        ]);
    }

    public function getEdit(Request $request)
    {
        $data = Model\Role::findOrFail($request->input('id', 0));
        return view('module::edit', ['data' => $data]);
    }

    public function postEdit(Request $request)
    {
        $data = Model\Role::findOrFail($request->input('id', 0));
        $data->role = $request->input('role');
        $data->name = $request->input('name');
        $data->save();

        return data;
    }

    public function postDelete(Request $request)
    {
        Model\Role::whereIn('id', $request->input('ids'))->delete();
    }
}
