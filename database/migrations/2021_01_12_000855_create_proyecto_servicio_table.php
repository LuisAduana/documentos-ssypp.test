<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProyectoServicioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proyecto_servicio', function (Blueprint $table) {
            $table->id();
            $table->string('num_alumnos', 45);
            $table->string('actividades', 45);
            $table->string('horario', 100);
            $table->string('requisitos', 250);
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
        Schema::dropIfExists('proyecto_servicio');
    }
}
