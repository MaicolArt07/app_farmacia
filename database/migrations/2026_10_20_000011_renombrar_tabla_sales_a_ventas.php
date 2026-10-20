<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('sales', 'ventas');

        Schema::table('ventas', function ($table) {
            $table->renameColumn('sale_date', 'fecha_venta');
            $table->renameColumn('payment_method', 'metodo_pago');
            $table->renameColumn('discount', 'descuento');
            $table->renameColumn('amount_paid', 'monto_pagado');
            $table->renameColumn('change_amount', 'monto_cambio');
            $table->renameColumn('observation', 'observacion');
            $table->renameColumn('state', 'estado');
        });

        Schema::rename('sale_details', 'detalles_venta');

        Schema::table('detalles_venta', function ($table) {
            $table->renameColumn('sale_price', 'precio_venta');
        });
    }

    public function down(): void
    {
        Schema::table('detalles_venta', function ($table) {
            $table->renameColumn('precio_venta', 'sale_price');
        });

        Schema::rename('detalles_venta', 'sale_details');

        Schema::table('ventas', function ($table) {
            $table->renameColumn('fecha_venta', 'sale_date');
            $table->renameColumn('metodo_pago', 'payment_method');
            $table->renameColumn('descuento', 'discount');
            $table->renameColumn('monto_pagado', 'amount_paid');
            $table->renameColumn('monto_cambio', 'change_amount');
            $table->renameColumn('observacion', 'observation');
            $table->renameColumn('estado', 'state');
        });

        Schema::rename('ventas', 'sales');
    }
};
