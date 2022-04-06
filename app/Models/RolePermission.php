<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;

    function permission()
    {
        return $this->belongsTo('App\Models\Permission', 'permission_id');
    }

    function page()
    {
        return $this->belongsTo('App\Models\Page', 'page_id');
    }

    function role()
    {
        return $this->belongsTo('App\Models\Role', 'role_id');
    }
}
