<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SensorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sensors')->insert([
            [
                'id' => 1,
                'name' => 'DHT11',
                'description' => 'The DHT11 is a basic, ultra low-cost digital temperature and humidity sensor. It uses a capacitive humidity sensor and a thermistor to measure the surrounding air, and spits out a digital signal on the data pin (no analog input pins needed).',
                'image_url' => 'sensors-bak/dht11.png'
            ],
            [
                'id' => 2,
                'name' => 'DHT22',
                'description' => 'The DHT22 is a basic digital temperature and humidity sensor. It uses a capacitive humidity sensor and a thermistor to measure the surrounding air, and spits out a digital signal on the data pin, no analog input pins needed.',
                'image_url' => 'sensors-bak/dht22.png'
            ],
            [
                'id' => 3,
                'name' => 'HC-SR04',
                'description' => 'An ultrasonic sensor is an instrument that measures the distance to an object using ultrasonic sound waves. An ultrasonic sensor uses a transducer to send and receive ultrasonic pulses that relay back information about an object\'s proximity.',
                'image_url' => 'sensors-bak/hcsr04.png'
            ],
            [
                'id' => 4,
                'name' => 'MQ2',
                'description' => 'The Grove - Gas Sensor(MQ2) module is useful for gas leakage detection (home and industry). It is suitable for detecting H2, LPG, CH4, CO, Alcohol, Smoke or Propane.',
                'image_url' => 'sensors-bak/mq2.png'
            ],
            [
                'id' => 5,
                'name' => 'PIR',
                'description' => 'Passive infrared (PIR) sensors use a pair of pyroelectric sensors to detect heat energy in the surrounding environment. These two sensors sit beside each other, and when the signal differential between the two sensors changes (if a person enters the room, for example), the sensor will engage.',
                'image_url' => 'sensors-bak/pir.png'
            ],
        ]);

        DB::table('sensor_pins')->insert([
            // DHT11
            [
                'sensor_id'     =>    1,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    1,
                'remarks'       =>    'AOUT'
            ],

            // DHT22
            [
                'sensor_id'     =>    2,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    1,
                'remarks'       =>    'AOUT'
            ],

            // ULTRASONIC SENSOR [TRIG, ECHO]
            [
                'sensor_id'     =>    3,
                'pin_type_id'   =>    7,    // TRIG
                'pin_number'    =>    1,
                'remarks'       =>    'TRIG'
            ],
            [
                'sensor_id'     =>    3,
                'pin_type_id'   =>    8,    // ECHO
                'pin_number'    =>    2,
                'remarks'       =>    'ECHO'
            ],

            // MQ2 [A, D]
            [
                'sensor_id'     =>    4,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    1,
                'remarks'       =>    'AOUT'
            ],
            [
                'sensor_id'     =>    4,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    2,
                'remarks'       =>    'DOUT'
            ],

            // PIR
            [
                'sensor_id'     =>    5,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    1,
                'remarks'       =>    'DOUT'
            ],
        ]);

        DB::table('sensor_columns')->insert([
            // DHT11
            [
                'sensor_id'  =>    1,
                'column' => 'TEMP',
            ],
            [
                'sensor_id'  =>    1,
                'column' => 'HUM',
            ],

            // DHT22
            [
                'sensor_id'     =>    2,
                'column' => 'TEMP',
            ],
            [
                'sensor_id'     =>    2,
                'column' => 'HUM',
            ],

            // ULTRASONIC SENSOR
            [
                'sensor_id'     =>    3,
                'column' => 'DIST',
            ],

            // MQ2 [A, D]
            [
                'sensor_id'     =>    4,
                'column' => 'AVAL',
            ],

            // PIR
            [
                'sensor_id'     =>    5,
                'column' => 'DVAL',
            ],
        ]);
    }
}
