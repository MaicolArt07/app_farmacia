<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('suppliers', 'proveedores');

        Schema::table('proveedores', function ($table) {
            $table->renameColumn('company_name', 'razon_social');
            $table->renameColumn('contact_position', 'cargo_contacto');
            $table->renameColumn('state', 'estado');
        });
    }

    public function down(): void
    {
        Schema::table('proveedores', function ($table) {
            $table->renameColumn('razon_social', 'company_name');
            $table->renameColumn('cargo_contacto', 'contact_position');
            $table->renameColumn('estado', 'state');
        });

        Schema::rename('proveedores', 'suppliers');
    }
};
