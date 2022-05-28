<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserActuatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_actuators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('actuator_id')->constrained('actuators')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('user_board_id')->constrained('user_boards')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('operating_value')->default(0);
            $table->boolean('is_switched_on')->default(false);
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
        Schema::dropIfExists('user_actuators');
    }
}
