<?php

use App\Models\Admin\Role;
use App\Models\Core\Model;
use App\Services\UsersService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 
        $tableNames = config('permission.table_names');
        $user = new UsersService();

        // Role::deleted();
        $user->role->truncate();
        // DB::table($tableNames['roles'])->truncate();

        for ($i = 1; $i <= 10; $i++) {
            $username = "test";
            $username = "test";
            switch ($i) {
                case 1:
                    $username = "Super Admin";
                    break;
                case 2:
                    $username = "Customer";
                    break;
                case 3:
                    $username = "Vendor";
                    break;
                case 4:
                    $username = "Delivery";
                    break;
                default:
                    $username = "Test" . $i;
                    break;
            }
            $attribute = array(
                'name' => $username,
                'isActive' => 1,
            );
            $attribute = Model::setCommonDefault($attribute);
            
        }
    }
}
