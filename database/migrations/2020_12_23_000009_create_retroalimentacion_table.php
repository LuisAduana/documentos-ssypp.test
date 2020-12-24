<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetroalimentacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retroalimentacion', function (Blueprint $table) {
            $table->id();
            $table->string('mensaje', 250)->nullable();
            $table->dateTime('fecha_envio')->nullable();
            $table->foreignId('documento_id')->constrained('documento');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('retroalimentacion');
    }
}
