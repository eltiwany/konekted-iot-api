<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PinTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pin_types')->insert([
            [
                'id' => 1,
                'type' => 'D'
            ],
            [
                'id' => 2,
                'type' => 'A'
            ],
            [
                'id' => 3,
                'type' => 'SPI'
            ],
            [
                'id' => 4,
                'type' => 'GPIO'
            ],
            [
                'id' => 5,
                'type' => 'SDA'
            ],
            [
                'id' => 6,
                'type' => 'SCL'
            ],
            [
                'id' => 7,
                'type' => 'TRIG'
            ],
            [
                'id' => 8,
                'type' => 'ECHO'
            ],
        ]);
    }
}
