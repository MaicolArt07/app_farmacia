<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('products', 'productos');

        Schema::table('productos', function ($table) {
            $table->renameColumn('name', 'nombre');
            $table->renameColumn('generic_name', 'nombre_generico');
            $table->renameColumn('concentration', 'concentracion');
            $table->renameColumn('description', 'descripcion');
            $table->renameColumn('sale_price', 'precio_venta');
            $table->renameColumn('minimum_stock', 'stock_minimo');
            $table->renameColumn('requires_prescription', 'requiere_receta');
            $table->renameColumn('state', 'estado');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function ($table) {
            $table->renameColumn('nombre', 'name');
            $table->renameColumn('nombre_generico', 'generic_name');
            $table->renameColumn('concentracion', 'concentration');
            $table->renameColumn('descripcion', 'description');
            $table->renameColumn('precio_venta', 'sale_price');
            $table->renameColumn('stock_minimo', 'minimum_stock');
            $table->renameColumn('requiere_receta', 'requires_prescription');
            $table->renameColumn('estado', 'state');
        });

        Schema::rename('productos', 'products');
    }
};
