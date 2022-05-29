<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('boards')->insert([
            [
                'id' => 1,
                'name' => 'NodeMCU ESP8266',
                'description' => 'NodeMCU is an open source Lua based firmware for the ESP8266 WiFi SOC from Espressif and uses an on-module flash-based SPIFFS file system. NodeMCU is implemented in C and is layered on the Espressif NON-OS SDK.',
                'image_url' => 'boards-bak/nodemcu.png'
            ],
            [
                'id' => 2,
                'name' => 'Arduino UNO',
                'description' => 'Arduino/Genuino Uno is a microcontroller board based on the ATmega328P (datasheet). It has 14 digital input/output pins (of which 6 can be used as PWM outputs), 6 analog inputs, a 16 MHz quartz crystal, a USB connection, a power jack, an ICSP header and a reset button. It contains everything needed to support the microcontroller; simply connect it to a computer with a USB cable or power it with a AC-to-DC adapter or battery to get started.. You can tinker with your UNO without worring too much about doing something wrong, worst case scenario you can replace the chip for a few dollars and start over again.',
                'image_url' => 'boards-bak/arduino-uno.png'
            ],
            [
                'id' => 3,
                'name' => 'Arduino UNO Wifi Rev2',
                'description' => 'The Arduino UNO WiFi Rev.2 is the easiest point of entry to basic IoT with the standard form factor of the UNO family. Whether you are looking at building a sensor network connected to your office or home router, or if you want to create a Bluetooth® Low Energy device sending data to a cellphone, the Arduino UNO WiFi Rev.2 is your one-stop-solution for many of the basic IoT application scenarios.',
                'image_url' => 'boards-bak/arduino-uno-wifi.png'
            ],
        ]);

        DB::table('board_pins')->insert([
            // NodeMCU [A0, GPIO1 - GPIO16]
            [
                'board_id'      =>    1,
                'pin_type_id'   =>    4,    // GPIO
                'pin_number'    =>    0,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    1,
                'pin_type_id'   =>    4,    // GPIO
                'pin_number'    =>    1,
                'remarks'       =>    'TX'
            ],
            [
                'board_id'      =>    1,
                'pin_type_id'   =>    4,
                'pin_number'    =>    2,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    1,
                'pin_type_id'   =>    4,
                'pin_number'    =>    3,
                'remarks'       =>    'RX'
            ],
            [
                'board_id'      =>    1,
                'pin_type_id'   =>    4,
                'pin_number'    =>    4,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    1,
                'pin_type_id'   =>    4,
                'pin_number'    =>    5,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    1,
                'pin_type_id'   =>    4,
                'pin_number'    =>    9,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    1,
                'pin_type_id'   =>    4,
                'pin_number'    =>    10,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    1,
                'pin_type_id'   =>    4,
                'pin_number'    =>    12,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    1,
                'pin_type_id'   =>    4,
                'pin_number'    =>    13,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    1,
                'pin_type_id'   =>    4,
                'pin_number'    =>    14,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    1,
                'pin_type_id'   =>    4,
                'pin_number'    =>    15,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    1,
                'pin_type_id'   =>    4,
                'pin_number'    =>    16,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    1,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    0,
                'remarks'       =>    ''
            ],


            // Arduino UNO [D0 - D13, A0 - A6]
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    0,
                'remarks'       =>    'RX'
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    1,
                'remarks'       =>    'TX'
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    2,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    3,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    4,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    5,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    6,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    7,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    8,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    9,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    10,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    11,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    12,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    13,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    0,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    1,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    2,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    3,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    2,    // A, SDA
                'pin_number'    =>    4,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    2,    // A, SCL
                'pin_number'    =>    5,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    2,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    6,
                'remarks'       =>    ''
            ],


            // Arduino UNO Wifi [D0 - D13, A0 - A6]
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    0,
                'remarks'       =>    'RX'
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    1,
                'remarks'       =>    'TX'
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    2,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    3,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    4,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    5,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    6,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    7,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    8,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    9,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    10,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    11,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    12,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    1,    // D
                'pin_number'    =>    13,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    0,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    1,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    2,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    3,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    2,    // A, SDA
                'pin_number'    =>    4,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    2,    // A, SCL
                'pin_number'    =>    5,
                'remarks'       =>    ''
            ],
            [
                'board_id'      =>    3,
                'pin_type_id'   =>    2,    // A
                'pin_number'    =>    6,
                'remarks'       =>    ''
            ],
        ]);
    }
}
