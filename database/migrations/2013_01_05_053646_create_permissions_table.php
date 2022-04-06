<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description');
            $table->timestamps();
        });

        DB::table('permissions')->insert([
            [
                'id' => 1,
                'name' => 'r',
                'description' => 'read'
            ],
            [
                'id' => 2,
                'name' => 'v',
                'description' => 'view'
            ],
            [
                'id' => 3,
                'name' => 'w',
                'description' => 'write'
            ],
            [
                'id' => 4,
                'name' => 'd',
                'description' => 'delete'
            ],
            [
                'id' => 5,
                'name' => 'a',
                'description' => 'all'
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
        Schema::dropIfExists('permissions');
    }
}
