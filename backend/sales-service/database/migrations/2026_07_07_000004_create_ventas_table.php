<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospecto_id')->constrained('prospectos');
            $table->foreignId('vehiculo_id')->constrained('vehiculos');
            $table->foreignId('vendedor_id')->constrained('vendedores');
            $table->decimal('monto', 10, 2);
            $table->enum('estado', ['efectiva', 'fallida']);
            $table->string('motivo_perdida', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ventas');
    }
};
