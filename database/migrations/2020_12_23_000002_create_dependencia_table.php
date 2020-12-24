<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDependenciaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dependencia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_dependencia', 230);
            $table->string('nombre_contacto', 200);
            $table->string('direccion', 250);
            $table->string('ciudad', 120);
            $table->string('correo', 130);
            $table->string('num_contacto', 20);
            $table->string('sector', 50);
            $table->string('num_us_directos', 30);
            $table->string('num_us_indirectos', 30);
            $table->string('estado', 15);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dependencia');
    }
}
