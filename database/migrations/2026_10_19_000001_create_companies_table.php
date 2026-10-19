<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('nit', 50);
            $table->string('razon_social', 200);
            $table->string('nombre_comercial', 150)->nullable();
            $table->string('actividad_economica', 150)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('logo_path', 255)->nullable();

            // Datos propios del sistema de facturacion (modalidad Computarizada en Linea)
            $table->string('codigo_sistema', 100)->nullable();
            $table->string('cuis', 100)->nullable();
            $table->string('ambiente', 20)->default('PRUEBA'); // PRUEBA | PRODUCCION

            $table->boolean('state')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
