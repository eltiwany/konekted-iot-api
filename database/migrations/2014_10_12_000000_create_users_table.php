<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->foreignId('role_id')->nullable()->constrained('roles')->onUpdate('cascade')->onDelete('set null');
            $table->integer('is_active')->default(0)->comment("0 = inactive, 1 = active");
            $table->integer('incorrect_login_attempt')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Konekted Admin',
                'role_id' => 1,
                'email' => 'admin@konekted.com',
                'password' => bcrypt('admin12345'),
                'is_active' => 1
            ],
            [
                'id' => 2,
                'name' => 'Ali Abdulla Saleh',
                'role_id' => 1,
                'email' => 'admin@nafuutronics.com',
                'password' => bcrypt('admin12345'),
                'is_active' => 1
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
        Schema::dropIfExists('users');
    }
}
