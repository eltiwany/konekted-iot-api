<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('pages')->insert([
            [
                'id' => 1,
                'name' => 'All'
            ],
            [
                'id' => 2,
                'name' => 'Users'
            ],
            [
                'id' => 3,
                'name' => 'Roles'
            ],
            [
                'id' => 4,
                'name' => 'Permissions'
            ],
            [
                'id' => 5,
                'name' => 'Pages'
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pages');
    }
}
