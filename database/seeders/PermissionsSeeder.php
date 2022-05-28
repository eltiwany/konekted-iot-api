<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->insert([
            [
                'id' => 1,
                'name' => 'r',
                'description' => 'read'
            ],
            [
                'id' => 2,
                'name' => 'v',
                'description' => 'view'
            ],
            [
                'id' => 3,
                'name' => 'w',
                'description' => 'write'
            ],
            [
                'id' => 4,
                'name' => 'd',
                'description' => 'delete'
            ],
            [
                'id' => 5,
                'name' => 'a',
                'description' => 'all'
            ],
        ]);
    }
}
