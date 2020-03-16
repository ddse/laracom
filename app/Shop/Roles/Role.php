<?php

namespace App\Shop\Roles;

use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{
    protected $fillable = [
        'name',
        'display_name',
        'description'
    ];
}
