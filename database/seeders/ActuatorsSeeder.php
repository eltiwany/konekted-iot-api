<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActuatorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('actuators')->insert([
            [
                'id' => 1,
                'name' => 'BULB',
                'description' => '',
                'image_url' => 'actuators-bak/bulb.svg'
            ],
            [
                'id' => 2,
                'name' => 'ENGINE',
                'description' => '',
                'image_url' => 'actuators-bak/engine.svg'
            ],
            [
                'id' => 3,
                'name' => 'FAN',
                'description' => '',
                'image_url' => 'actuators-bak/fan.svg'
            ],
            [
                'id' => 4,
                'name' => 'MOTOR',
                'description' => '',
                'image_url' => 'actuators-bak/motor.svg'
            ],

        ]);

        DB::table('actuator_pins')->insert([
            // BULT
            [
                'actuator_id'   =>    1,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    1,
                'remarks'       =>    'DOUT'
            ],

            // DHT22
            [
                'actuator_id'   =>    2,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    1,
                'remarks'       =>    'DOUT'
            ],

            // FAN
            [
                'actuator_id'   =>    3,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    1,
                'remarks'       =>    'AOUT'
            ],

            // MOTOR
            [
                'actuator_id'   =>    4,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    1,
                'remarks'       =>    'AOUT'
            ],
        ]);

        DB::table('actuator_columns')->insert([
            [
                'actuator_id'  =>  1,
                'column' => 'DATA',
            ],
            [
                'actuator_id'  =>  2,
                'column' => 'DATA',
            ],
            [
                'actuator_id'  =>  3,
                'column' => 'DATA',
            ],
            [
                'actuator_id'  =>  4,
                'column' => 'DATA',
            ],
        ]);
    }
}
