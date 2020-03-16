<?php

namespace App\Shop\Roles\Repositories;

use App\Shop\Base\Repositories\BaseRepositoryInterface;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    public function createRole(array $data) : Role;

    public function listRoles(string $order = 'id', string $sort = 'desc') : Collection;

    public function findRoleById(int $id);

    public function updateRole(array $data) : bool;

    public function deleteRoleById() : bool;

    public function attachToPermission(Permission $permission);

    public function attachToPermissions(... $permissions);

    public function syncPermissions(array $ids);

    public function listPermissions() : Collection;
}
