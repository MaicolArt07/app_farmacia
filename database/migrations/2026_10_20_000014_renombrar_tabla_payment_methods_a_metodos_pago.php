<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('payment_methods', 'metodos_pago');

        Schema::table('metodos_pago', function ($table) {
            $table->renameColumn('name', 'nombre');
            $table->renameColumn('state', 'estado');
        });
    }

    public function down(): void
    {
        Schema::table('metodos_pago', function ($table) {
            $table->renameColumn('nombre', 'name');
            $table->renameColumn('estado', 'state');
        });

        Schema::rename('metodos_pago', 'payment_methods');
    }
};
