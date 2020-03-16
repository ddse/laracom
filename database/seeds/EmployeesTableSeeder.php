<?php

use App\Shop\Employees\Employee;
use App\Shop\Roles\Repositories\RoleRepository;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Shop\Permissions\Permission;
use App\Shop\Roles\Role;

class EmployeesTableSeeder extends Seeder
{
    public function run()
    {
        $tableNames = config('permission.table_names');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($tableNames as $key => $val) {
            DB::table($val)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // DB::table('model_has_permissions')->truncate();
        // (new Permission())->truncate();
        // (new Role())->truncate();
        (new Employee())->truncate();
        /* ********************* */
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'users_manage']);

        $role = Role::create(['name' => 'administrator']);
        $role->givePermissionTo('users_manage');

        // $user = User::create([
        //     'name' => 'Admin',
        //     'email' => 'admin@admin.com',
        //     'password' => bcrypt('password')
        // ]);
        // $user->assignRole('administrator');

        /* ********************* */

        $createProductPerm = factory(Permission::class)->create([
            'name' => 'create-product',
            //'display_name' => 'Create product'
        ]);

        $viewProductPerm = factory(Permission::class)->create([
            'name' => 'view-product',
            //'display_name' => 'View product'
        ]);

        $updateProductPerm = factory(Permission::class)->create([
            'name' => 'update-product',
            // 'display_name' => 'Update product'
        ]);

        $deleteProductPerm = factory(Permission::class)->create([
            'name' => 'delete-product',
            // 'display_name' => 'Delete product'
        ]);

        $updateOrderPerm = factory(Permission::class)->create([
            'name' => 'update-order',
            // 'display_name' => 'Update order'
        ]);

        $super = factory(Role::class)->create([
            'name' => 'superadmin',
        ]);

        // $role = Role::create(['name' => 'superadmin']);

        $employee = factory(Employee::class)->create([
            'email' => 'john@doe.com'
        ]);

        // $role->givePermissionTo(['name' => $createProductPerm->name]);

        $roleSuperRepo = new RoleRepository($super);
        // $role = Role::create(['name' => 'administrator']);
        // $role->givePermissionTo('users_manage');
        //['name' => 'administrator']
        $roleSuperRepo->attachToPermission($createProductPerm);
        $roleSuperRepo->attachToPermission($viewProductPerm);
        $roleSuperRepo->attachToPermission($updateProductPerm);
        $roleSuperRepo->attachToPermission($deleteProductPerm);
        $roleSuperRepo->attachToPermission($updateOrderPerm);

        $employee->assignRole($super->name);
        // $employee->roles()->save($super);

        $employee = factory(Employee::class)->create([
            'email' => 'admin@admin.com'
        ]);

        $admin = factory(Role::class)->create([
            'name' => 'admin',
        ]);

        $roleAdminRepo = new RoleRepository($admin);
        $roleAdminRepo->attachToPermission($createProductPerm);
        $roleAdminRepo->attachToPermission($viewProductPerm);
        $roleAdminRepo->attachToPermission($updateProductPerm);
        $roleAdminRepo->attachToPermission($deleteProductPerm);
        $roleAdminRepo->attachToPermission($updateOrderPerm);

        $employee->roles()->save($admin);

        $employee = factory(Employee::class)->create([
            'email' => 'clerk@doe.com'
        ]);

        $clerk = factory(Role::class)->create([
            'name' => 'clerk',
        ]);

        $roleClerkRepo = new RoleRepository($clerk);
        $roleClerkRepo->attachToPermission($createProductPerm);
        $roleClerkRepo->attachToPermission($viewProductPerm);
        $roleClerkRepo->attachToPermission($updateProductPerm);

        $employee->roles()->save($clerk);
    }
}
