<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Los servicios (a diferencia del catálogo de productos) NO se comparten
     * entre sucursales: cada una puede cobrar un precio distinto por el
     * mismo tipo de servicio, así que cada sucursal maneja su propia fila.
     */
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->foreignId('id_sucursal')->nullable()->after('id')->constrained('sucursales')->nullOnDelete();
        });

        // Backfill: los servicios existentes son todos de La Moscú (numero_sucursal = 0).
        DB::statement('
            UPDATE servicios
            INNER JOIN sucursales ON sucursales.numero_sucursal = 0
            SET servicios.id_sucursal = sucursales.id
            WHERE servicios.id_sucursal IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_sucursal');
        });
    }
};
