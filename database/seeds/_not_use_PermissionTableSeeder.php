<?php

use App\Models\Admin\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tableNames = config('permission.table_names');
        DB::table($tableNames['permissions'])->delete();
        $permissions = [
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
            'dashboard_view',

            'manufacturer_view',
            'manufacturer_create',
            'manufacturer_update',
            'manufacturer_delete',

            'categories_view',
            'categories_create',
            'categories_update',
            'categories_delete',

            'products_view',
            'products_create',
            'products_update',
            'products_delete',

            'news_view',
            'news_create',
            'news_update',
            'news_delete',

            'view_media',
            'add_media',
            'edit_media',
            'delete_media',

            'customers_view',
            'customers_create',
            'customers_update',
            'customers_delete',

            'tax_location_view',
            'tax_location_create',
            'tax_location_update',
            'tax_location_delete',

            'coupons_view',
            'coupons_create',
            'coupons_update',
            'coupons_delete',

            'notifications_view',
            'notifications_send',

            'orders_view',
            'orders_confirm',

            'shipping_methods_view',
            'shipping_methods_update',

            'payment_methods_view',
            'payment_methods_update',

            'reports_view',

            'website_setting_view',
            'website_setting_update',

            'application_setting_view',
            'application_setting_update',


            'general_setting_view',
            'general_setting_update',

            'manage_admins_view',
            'manage_admins_create',
            'manage_admins_update',
            'manage_admins_delete',


            'language_view',
            'language_create',
            'language_update',
            'language_delete',

            'profile_view',
            'profile_update',

            'admintype_view',
            'admintype_create',
            'admintype_update',
            'manage_admins_role',
        ];


        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
