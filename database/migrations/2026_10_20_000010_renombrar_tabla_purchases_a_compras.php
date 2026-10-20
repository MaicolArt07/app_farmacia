<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('purchases', 'compras');

        Schema::table('compras', function ($table) {
            $table->renameColumn('invoice_number', 'numero_factura');
            $table->renameColumn('purchase_date', 'fecha_compra');
            $table->renameColumn('observation', 'observacion');
            $table->renameColumn('state', 'estado');
        });

        Schema::rename('purchase_details', 'detalles_compra');

        Schema::table('detalles_compra', function ($table) {
            $table->renameColumn('batch_code', 'codigo_lote');
            $table->renameColumn('expiration_date', 'fecha_vencimiento');
            $table->renameColumn('purchase_price', 'precio_compra');
            $table->renameColumn('sale_price', 'precio_venta');
        });
    }

    public function down(): void
    {
        Schema::table('detalles_compra', function ($table) {
            $table->renameColumn('codigo_lote', 'batch_code');
            $table->renameColumn('fecha_vencimiento', 'expiration_date');
            $table->renameColumn('precio_compra', 'purchase_price');
            $table->renameColumn('precio_venta', 'sale_price');
        });

        Schema::rename('detalles_compra', 'purchase_details');

        Schema::table('compras', function ($table) {
            $table->renameColumn('numero_factura', 'invoice_number');
            $table->renameColumn('fecha_compra', 'purchase_date');
            $table->renameColumn('observacion', 'observation');
            $table->renameColumn('estado', 'state');
        });

        Schema::rename('compras', 'purchases');
    }
};
