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
            $row->users = $row->users()->count();
            $row->state = $children->contains($row->id) ? 'closed' : 'open';
            return $row;
        });
    }

    public function getCreate(Request $request)
    {
        return view('module::create', ['data' => collect($request->all())]);
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
        $data->parent = $request->input('parent', 0);
        $data->role = $request->input('role');
        $data->name = $request->input('name');
        $data->save();

        return $data;
    }

    public function postDelete(Request $request)
    {
        Model\Role::whereIn('id', $request->input('ids'))->delete();
    }

    public function getAcl(Request $request)
    {
        $data = Model\Acl::where('role_id', $request->input('id', 0))
            ->get()
            ->each(function ($row) {
                $row->key = implode('-', [$row->group, $row->module, $row->alias]);
            })
            ->unique('key')
            ->values();
        return view('module::acl', ['data' => $data]);
    }

    public function postAcl(Request $request)
    {
        $id = $request->input('id', 0);
        $acl = collect(json_decode($request->input('acl', '[]'), true));
        Model\Acl::where('role_id', $id)->delete();
        $acl->map(function ($row) use ($id) {
            array_set($row, 'role_id', $id);
            return Model\Acl::create($row);
        });
    }

    public function postModule()
    {
        $children = Model\Module::where('menu', true)
            ->get()
            ->map(function ($row) {
                $acl = collect($row->acl)
                    ->filter(function ($status) {
                        return $status;
                    })
                    ->map(function ($status, $alias) {
                        return $alias;
                    })
                    ->values();

                return [
                    'parent' => $row->group,
                    'name' => $row->name,
                    'group' => $row->module_group,
                    'module' => $row->module_module,
                    'alias' => '*',
                    'key' => implode('-', [$row->module_group, $row->module_module, '*']),
                    'children' => $acl->map(function ($alias) use ($row) {
                        return [
                            'parent' => $row->name,
                            'name' => $alias,
                            'group' => $row->module_group,
                            'module' => $row->module_module,
                            'alias' => $alias,
                            'key' => implode('-', [$row->module_group, $row->module_module, $alias]),
                        ];
                    }),
                ];
            })
            ->groupBy('group')
            ->map(function ($children, $group) {
                $name = array_get($children, '0.parent');
                return [
                    'parent' => null,
                    'name' => $name,
                    'group' => $group,
                    'module' => '*',
                    'alias' => '*',
                    'key' => implode('-', [$group, '*', '*']),
                    'children' => $children,
                ];
            })
            ->values();

        return [[
            'parent' => null,
            'name' => '所有权限',
            'group' => '*',
            'module' => '*',
            'alias' => '*',
            'key' => implode('-', ['*', '*', '*']),
            'children' => $children,
        ]];
    }

    public function postCombotree()
    {
        return [[
            'id' => 0,
            'parent' => 0,
            'name' => '一级角色',
            'children' => Model\Role::where('parent', 0)->get()->each(function ($row) {
                $row->children;
            })
        ]];
    }

    public function postExist(Request $request)
    {
        $reverse = $request->input('reverse', false);
        $exist = false;
        $except = $request->input('except', null);

        switch ($request->input('type', 'role')) {
            case 'role':
                $role = $request->input('role', '');
                if ($except == $role) {
                    $exist = false;
                } else {
                    $exist = Model\Role::where('role', $role)->count();
                }
                break;
        }
        return var_export($exist && !$reverse || !$exist && $reverse, true);
    }

}
