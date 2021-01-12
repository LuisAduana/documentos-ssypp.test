<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProyectoPracticaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proyecto_practica', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_proyecto', 250);
            $table->text('descripcion_general');
            $table->text('objetivo_general');
            $table->text('objetivos_inmediatos');
            $table->text('objetivos_mediatos');
            $table->string('metodologia', 50)->nullable();
            $table->string('recursos', 250)->nullable();
            $table->string('actividades_funcionales', 250)->nullable();
            $table->string('responsabilidades', 200)->nullable();
            $table->string('duracion', 200)->nullable();
            $table->string('horario', 200)->nullable();
            $table->string('estado_proyecto', 50);
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
        Schema::dropIfExists('proyecto_practica');
    }
}
