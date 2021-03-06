<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumnoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alumno', function (Blueprint $table) {
            $table->id();
            $table->string('matricula', 9)->unique();
            $table->string('bloque', 1);
            $table->string('seccion', 1);
            $table->string('proyectos', 50);
            $table->foreignId('users_id')->constrained('users');
            $table->foreignId('profesor_id')->constrained('profesor');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alumno');
    }
}
