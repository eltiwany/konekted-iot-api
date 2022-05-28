<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            [
                'id' => 1,
                'name' => 'admin',
                'is_default' => 0
            ],
            [
                'id' => 2,
                'name' => 'user',
                'is_default' => 1
            ]
        ]);
    }
}
