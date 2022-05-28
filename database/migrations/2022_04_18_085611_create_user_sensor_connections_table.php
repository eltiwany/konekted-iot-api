<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSensorConnectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_sensor_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_sensor_id')->constrained('user_sensors')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('sensor_pin_id')->constrained('sensor_pins')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('board_pin_id')->constrained('board_pins')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_sensor_connections');
    }
}
