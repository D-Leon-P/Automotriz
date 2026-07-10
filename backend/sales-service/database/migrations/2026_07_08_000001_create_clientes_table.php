<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_documento', ['DNI', 'RUC', 'CEX'])->default('DNI');
            $table->string('nombre', 50)->nullable();
            $table->string('apellido', 50)->nullable();
            $table->string('razon_social', 150)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('email', 100)->nullable()->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('documento', 20)->unique();
            $table->string('direccion', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clientes');
    }
};
