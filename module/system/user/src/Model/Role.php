<?php

namespace Module\System\User\Model;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'easyui_roles';

    public $timestamps = true;

    public function users()
    {
        return $this->belongsToMany(
            'Module\System\User\Model\User',
            'easyui_role_user',
            'role_id',
            'user_id'
        );
    }

    /**
     * 上下级关联
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function child()
    {
        return $this->hasMany('Module\System\Role\Model\Role', 'parent', 'id');
    }

    /**
     * 递归查询
     * @return $this
     */
    public function children()
    {
        return $this->child()->with('children');
    }

}
