<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActuatorPinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actuator_pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actuator_id')->constrained('actuators')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('pin_type_id')->constrained('pin_types')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('pin_number');
            $table->string('remarks')->default('-');
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
        Schema::dropIfExists('actuator_pins');
    }
}
