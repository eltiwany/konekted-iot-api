<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserActuatorConnectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_actuator_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_actuator_id')->constrained('user_actuators')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('actuator_pin_id')->constrained('actuator_pins')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('user_actuator_connections');
    }
}
