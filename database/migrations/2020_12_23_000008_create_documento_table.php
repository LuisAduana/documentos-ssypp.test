<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documento', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_documento', 80);
            $table->string('path', 250);
            $table->string('tipo_documento', 10);
            $table->string('estado_documento', 10);
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
        Schema::dropIfExists('documento');
    }
}
