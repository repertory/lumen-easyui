<?php

namespace Module\System\User\Model;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    public $timestamps = true;

    protected $fillable = [
        'name', 'email', 'password'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    public function roles()
    {
        return $this->belongsToMany(
            'Module\System\User\Model\Role',
            'easyui_role_user',
            'user_id',
            'role_id'
        );
    }

}
