<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pages')->insert([
            [
                'id' => 1,
                'name' => 'All'
            ],
            [
                'id' => 2,
                'name' => 'Users'
            ],
            [
                'id' => 3,
                'name' => 'Roles'
            ],
            [
                'id' => 4,
                'name' => 'Permissions'
            ],
            [
                'id' => 5,
                'name' => 'Pages'
            ],
            [
                'id' => 6,
                'name' => 'Boards'
            ],
            [
                'id' => 7,
                'name' => 'Sensors'
            ],
            [
                'id' => 8,
                'name' => 'Actuators'
            ],
            [
                'id' => 9,
                'name' => 'Config Boards'
            ],
            [
                'id' => 10,
                'name' => 'Config Sensors'
            ],
            [
                'id' => 11,
                'name' => 'Config Actuators'
            ],
            [
                'id' => 12,
                'name' => 'Control Actuators'
            ],
            [
                'id' => 13,
                'name' => 'Monitor Hardwares'
            ],
            [
                'id' => 14,
                'name' => 'Automate Hardwares'
            ],
            [
                'id' => 15,
                'name' => 'Change Password'
            ],
        ]);
    }
}
