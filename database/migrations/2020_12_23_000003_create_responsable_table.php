<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResponsableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('responsable', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_responsable', 120);
            $table->string('cargo', 100);
            $table->string('correo', 130)->unique();
            $table->string('num_contacto', 20);
            $table->string('estado', 15);
            $table->foreignId('dependencia_id')->constrained('dependencia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('responsable');
    }
}
