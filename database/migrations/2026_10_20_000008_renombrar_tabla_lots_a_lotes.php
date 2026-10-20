<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('lots', 'lotes');

        Schema::table('lotes', function ($table) {
            $table->renameColumn('batch_code', 'codigo_lote');
            $table->renameColumn('purchase_price', 'precio_compra');
            $table->renameColumn('sale_price', 'precio_venta');
            $table->renameColumn('quantity_in', 'cantidad_ingresada');
            $table->renameColumn('quantity_available', 'cantidad_disponible');
            $table->renameColumn('expiration_date', 'fecha_vencimiento');
            $table->renameColumn('location', 'ubicacion');
            $table->renameColumn('state', 'estado');
        });
    }

    public function down(): void
    {
        Schema::table('lotes', function ($table) {
            $table->renameColumn('codigo_lote', 'batch_code');
            $table->renameColumn('precio_compra', 'purchase_price');
            $table->renameColumn('precio_venta', 'sale_price');
            $table->renameColumn('cantidad_ingresada', 'quantity_in');
            $table->renameColumn('cantidad_disponible', 'quantity_available');
            $table->renameColumn('fecha_vencimiento', 'expiration_date');
            $table->renameColumn('ubicacion', 'location');
            $table->renameColumn('estado', 'state');
        });

        Schema::rename('lotes', 'lots');
    }
};
