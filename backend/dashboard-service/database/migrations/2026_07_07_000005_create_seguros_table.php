<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seguros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas');
            $table->string('tipo_seguro', 100);
            $table->decimal('prima_esperada', 10, 2);
            $table->decimal('prima_real', 10, 2)->nullable();
            $table->enum('estado', ['prospectado', 'vendido'])->default('prospectado');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('seguros');
    }
};
