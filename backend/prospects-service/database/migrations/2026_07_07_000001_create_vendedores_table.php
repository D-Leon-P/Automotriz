<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->timestamps();
        });

        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->timestamps();
        });

        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->foreignId('rol_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permiso_id')->constrained('permisos')->onDelete('cascade');
            $table->primary(['rol_id', 'permiso_id']);
        });

        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->foreignId('rol_id')->constrained('roles')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('empleados');
        Schema::dropIfExists('rol_permiso');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('roles');
    }
};
