<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RolesSeeder::class,
            PagesSeeder::class,
            PermissionsSeeder::class,
            RolePermissionsSeeder::class,
            UsersSeeder::class,
            PinTypesSeeder::class,
            BoardsSeeder::class,
            SensorsSeeder::class,
            ActuatorsSeeder::class,
        ]);
    }
}
