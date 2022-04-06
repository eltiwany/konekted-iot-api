<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicePinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('device_pin_type_id')->constrained('device_pin_types')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('device_pins');
    }
}
