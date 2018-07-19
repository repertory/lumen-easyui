<?php

namespace Module\System\User;

use Illuminate\Http\Request;
use LaravelModule\Controllers\Controller as BaseController;

class Controller extends BaseController
{

    public function getIndex(Request $request)
    {
        return view('module::index');
    }

    public function postIndex(Request $request)
    {
        return Model\User::paginate($request->input('rows', 10));
    }

    public function getCreate()
    {
        return view('module::create');
    }

    public function postCreate()
    {
        abort(403, '没有权限');
    }

    public function getEdit(Request $request)
    {
        $data = Model\User::findOrFail($request->input('id', 0));
        return view('module::edit', ['data' => $data]);
    }

}
