<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega id_sucursal (nullable) a las tablas operativas clave para
     * soportar múltiples sucursales. El catálogo de productos NO se toca:
     * sigue compartido entre sucursales. Lo que se separa por sucursal es
     * el stock físico (lotes), la caja y las ventas, y a qué sucursal
     * pertenece cada usuario.
     *
     * Se deja nullable a propósito: el backfill de datos existentes
     * (asignarlos todos a la sucursal "Moscú") lo hace la herramienta de
     * Configuraciones > Migración de Sucursales, no esta migración, para
     * que un Super Admin decida cuándo correrlo y pueda revisar antes.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('id_sucursal')->nullable()->after('id_persona')->constrained('sucursales')->nullOnDelete();
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->foreignId('id_sucursal')->nullable()->after('id_producto')->constrained('sucursales')->nullOnDelete();
        });

        Schema::table('cajas', function (Blueprint $table) {
            $table->foreignId('id_sucursal')->nullable()->after('id_usuario')->constrained('sucursales')->nullOnDelete();
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('id_sucursal')->nullable()->after('id_caja')->constrained('sucursales')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_sucursal');
        });

        Schema::table('cajas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_sucursal');
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_sucursal');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_sucursal');
        });
    }
};
