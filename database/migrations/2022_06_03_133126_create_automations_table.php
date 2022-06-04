<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_sensor_id')->constrained('user_sensors')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('sensor_column_id')->constrained('sensor_columns')->onUpdate('cascade')->onDelete('cascade');
            $table->string('comparison_operation')->default('E')->comment('E = Equal, NE = Not Equal, G = Greater, GE = Greater or Equal, L = Less, LE = Less or Equal');
            $table->string('value');
            $table->boolean('operating_value')->default(0);
            $table->boolean('is_switched_on')->default(true);
            $table->foreignId('user_actuator_id')->constrained('user_actuators')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('automations');
    }
}
