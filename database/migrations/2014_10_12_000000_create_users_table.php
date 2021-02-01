<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->string('correo', 150)->unique();
            $table->string('password', 120);
            $table->string('nombres', 90);
            $table->string('apellido_paterno', 45);
            $table->string('apellido_materno', 45);
            $table->string('estado', 15);
            $table->string('num_contacto', 20);
            $table->string('rol_usuario', 13);
        });
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
