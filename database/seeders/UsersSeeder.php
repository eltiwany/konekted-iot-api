<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Konekted Admin',
                'role_id' => 1,
                'email' => 'admin@konekted.com',
                'password' => bcrypt('admin12345'),
                'is_active' => 1
            ],
            [
                'id' => 2,
                'name' => 'Ali Abdulla Saleh',
                'role_id' => 1,
                'email' => 'admin@nafuutronics.com',
                'password' => bcrypt('admin12345'),
                'is_active' => 1
            ],
        ]);
    }
}
