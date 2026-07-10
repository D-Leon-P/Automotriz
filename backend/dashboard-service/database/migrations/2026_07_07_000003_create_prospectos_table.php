<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('prospectos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('email', 100);
            $table->string('telefono', 20)->nullable();
            $table->foreignId('vehiculo_id')->constrained('vehiculos');
            $table->enum('etapa', ['prospeccion', 'calificacion', 'negociacion', 'cierre'])->default('prospeccion');
            $table->foreignId('empleado_id')->constrained('empleados');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('prospectos');
    }
};
