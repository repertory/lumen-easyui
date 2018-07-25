<?php

namespace Module\System\User\Model;

use Illuminate\Database\Eloquent\Model;

class Acl extends Model
{
    protected $table = 'easyui_acls';

    public $timestamps = true;

    protected $fillable = [
        'group', 'module', 'alias', 'role_id', 'user_id',
    ];
}
