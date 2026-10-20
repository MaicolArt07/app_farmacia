<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('inventory_movements', 'movimientos_inventario');

        Schema::table('movimientos_inventario', function ($table) {
            $table->renameColumn('movement_type', 'tipo_movimiento');
            $table->renameColumn('description', 'descripcion');
        });

        Schema::rename('inventory_adjustments', 'ajustes_inventario');

        Schema::table('ajustes_inventario', function ($table) {
            $table->renameColumn('reason', 'motivo');
            $table->renameColumn('state', 'estado');
        });
    }

    public function down(): void
    {
        Schema::table('ajustes_inventario', function ($table) {
            $table->renameColumn('motivo', 'reason');
            $table->renameColumn('estado', 'state');
        });

        Schema::rename('ajustes_inventario', 'inventory_adjustments');

        Schema::table('movimientos_inventario', function ($table) {
            $table->renameColumn('tipo_movimiento', 'movement_type');
            $table->renameColumn('descripcion', 'description');
        });

        Schema::rename('movimientos_inventario', 'inventory_movements');
    }
};
