<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Equivalentes genéricos: productos.nombre_generico guarda el principio activo
 * y "via" la forma de administración (ORAL SOLIDO, ORAL LIQUIDO, INYECTABLE...).
 * Mismo principio activo + misma vía = intercambiable directo en Ventas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'via')) {
                $table->string('via', 30)->nullable()->after('concentracion');
            }
            $table->index('nombre_generico');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex(['nombre_generico']);
            $table->dropColumn('via');
        });
    }
};
