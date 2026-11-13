<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->foreignId('id_sucursal')->nullable()->after('id_usuario')->constrained('sucursales')->nullOnDelete();
        });

        // Backfill: cada compra toma la sucursal del usuario que la registró.
        DB::statement('
            UPDATE compras
            INNER JOIN users ON users.id = compras.id_usuario
            SET compras.id_sucursal = users.id_sucursal
            WHERE compras.id_sucursal IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_sucursal');
        });
    }
};
