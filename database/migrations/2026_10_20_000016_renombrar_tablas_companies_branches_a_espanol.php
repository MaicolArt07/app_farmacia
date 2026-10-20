<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('companies', 'empresas');

        Schema::table('empresas', function ($table) {
            $table->renameColumn('phone', 'telefono');
            $table->renameColumn('address', 'direccion');
            $table->renameColumn('state', 'estado');
        });

        Schema::rename('branches', 'sucursales');

        Schema::table('sucursales', function ($table) {
            $table->renameColumn('name', 'nombre');
            $table->renameColumn('address', 'direccion');
            $table->renameColumn('phone', 'telefono');
            $table->renameColumn('state', 'estado');
        });
    }

    public function down(): void
    {
        Schema::table('sucursales', function ($table) {
            $table->renameColumn('nombre', 'name');
            $table->renameColumn('direccion', 'address');
            $table->renameColumn('telefono', 'phone');
            $table->renameColumn('estado', 'state');
        });

        Schema::rename('sucursales', 'branches');

        Schema::table('empresas', function ($table) {
            $table->renameColumn('telefono', 'phone');
            $table->renameColumn('direccion', 'address');
            $table->renameColumn('estado', 'state');
        });

        Schema::rename('empresas', 'companies');
    }
};
