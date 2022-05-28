<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBoardPinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('board_pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('boards')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('pin_type_id')->constrained('pin_types')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('board_pins');
    }
}
