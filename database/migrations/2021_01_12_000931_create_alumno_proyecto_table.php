<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumnoProyectoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alumno_proyecto', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('tipo_proyecto', 10);
            $table->foreignId('alumno_id')->constrained('alumno');
            $table->foreignId('proyecto_id')->constrained('proyecto');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alumno_proyecto');
    }
}
