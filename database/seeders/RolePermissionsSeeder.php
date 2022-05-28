<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('role_permissions')->insert([
            // Admin
            [
                'role_id' => 1,
                'permission_id' => 5,
                'page_id' => 1,
            ],

            // Users
            [
                'role_id' => 2,
                'permission_id' => 5,
                'page_id' => 6,
            ],
            [
                'role_id' => 2,
                'permission_id' => 5,
                'page_id' => 7,
            ],
            [
                'role_id' => 2,
                'permission_id' => 5,
                'page_id' => 8,
            ],
            [
                'role_id' => 2,
                'permission_id' => 5,
                'page_id' => 12,
            ],
            [
                'role_id' => 2,
                'permission_id' => 5,
                'page_id' => 13,
            ],
            [
                'role_id' => 2,
                'permission_id' => 5,
                'page_id' => 14,
            ],
            [
                'role_id' => 2,
                'permission_id' => 5,
                'page_id' => 15,
            ],
        ]);
    }
}
