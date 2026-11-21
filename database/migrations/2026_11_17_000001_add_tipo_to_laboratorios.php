<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada registro de "laboratorios" puede ser un LABORATORIO o una MARCA.
     * El nombre visible del producto se arma con esto (Producto::nombre_completo):
     *   LABORATORIO -> "<NOMBRE> LAB.<LABORATORIO>"
     *   MARCA       -> "<NOMBRE> <MARCA>"
     * Solo agrega la columna; no modifica datos existentes.
     */
    public function up(): void
    {
        if (Schema::hasColumn('laboratorios', 'tipo')) {
            return;
        }

        Schema::table('laboratorios', function (Blueprint $table) {
            $table->enum('tipo', ['LABORATORIO', 'MARCA'])->default('LABORATORIO')->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('laboratorios', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
