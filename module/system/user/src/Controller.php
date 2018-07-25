<?php

namespace Module\System\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use LaravelModule\Controllers\Controller as BaseController;

class Controller extends BaseController
{

    public function getIndex(Request $request)
    {
        return view('module::index');
    }

    public function postIndex(Request $request, Model\User $user)
    {
        // 筛选
        if ($request->has('filterRules')) {
            $filterRules = collect(json_decode($request->input('filterRules'), true));
            $filterRules->each(function ($rule) use (&$user) {
                $field = array_get($rule, 'field');
                $value = array_get($rule, 'value');

                switch (array_get($rule, 'op')) {
                    case 'contains':
                        $user = $user->where($field, 'like', "%{$value}%");
                        break;
                    case 'notcontains':
                        $user = $user->where($field, 'not like', "%{$value}%");
                        break;
                    case 'beginwith':
                        $user = $user->where($field, 'like', "{$value}%");
                        break;
                    case 'endwith':
                        $user = $user->where($field, 'like', "{$value}%");
                        break;
                    case 'equal':
                        $user = $user->where($field, $value);
                        break;
                    case 'notequal':
                        $user = $user->where($field, '<>', $value);
                        break;
                    case 'less':
                        $user = $user->where($field, '<', $value);
                        break;
                    case 'lessorequal':
                        $user = $user->where($field, '<=', $value);
                        break;
                    case 'greater':
                        $user = $user->where($field, '>', $value);
                        break;
                    case 'greaterorequal':
                        $user = $user->where($field, '>=', $value);
                        break;
                    default:
                        $user = $user->where($field, $value);
                }
            });
        }

        // 排序
        if ($request->has('sort')) {
            $sorts = collect(explode(',', $request->input('sort', '')));
            $orders = collect(explode(',', $request->input('order', '')));
            $sorts->each(function ($sort, $index) use (&$user, $orders) {
                $user = $user->orderBy($sort, $orders->get($index));
            });
        }

        $data = $user->paginate($request->input('rows', 10));
        $data->each(function ($row) {
            $row->roles;
        });
        return $data;
    }

    public function getCreate()
    {
        return view('module::create');
    }

    public function postCreate(Request $request)
    {
        Model\User::insert([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'created_at' => date('c'),
            'updated_at' => date('c'),
        ]);
    }

    public function getEdit(Request $request)
    {
        $data = Model\User::findOrFail($request->input('id', 0));
        return view('module::edit', ['data' => $data]);
    }

    public function postEdit(Request $request)
    {
        $data = Model\User::findOrFail($request->input('id', 0));
        $data->name = $request->input('name');
        $data->email = $request->input('email');
        if ($request->input('password')) {
            $data->password = Hash::make($request->input('password'));
        }
        $data->save();

        return $data;
    }

    public function postDelete(Request $request)
    {
        Model\User::whereIn('id', $request->input('ids'))->delete();
    }

    public function getRole(Request $request)
    {
        $data = Model\User::findOrFail($request->input('id', 0));
        return view('module::role', ['data' => $data->roles]);
    }

    public function postRole(Request $request)
    {
        $data = Model\User::findOrFail($request->input('id', 0));
        $roles = $request->input('roles', []);
        $data->roles()->detach();
        $data->roles()->attach($roles);
    }

    public function postRoleCombotree()
    {
        return Model\Role::where('parent', 0)->get()->each(function ($row) {
            $row->children;
        });
    }

    public function getAcl(Request $request)
    {
        $data = Model\Acl::where('user_id', $request->input('id', 0))
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
        Model\Acl::where('user_id', $id)->delete();
        $acl->map(function ($row) use ($id) {
            array_set($row, 'user_id', $id);
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
}
