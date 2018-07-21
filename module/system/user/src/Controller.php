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

        return $user->paginate($request->input('rows', 10));
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

}
