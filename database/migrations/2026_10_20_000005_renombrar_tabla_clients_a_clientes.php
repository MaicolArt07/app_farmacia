<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('clients', 'clientes');

        Schema::table('clientes', function ($table) {
            $table->renameColumn('type', 'tipo');
            $table->renameColumn('state', 'estado');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function ($table) {
            $table->renameColumn('tipo', 'type');
            $table->renameColumn('estado', 'state');
        });

        Schema::rename('clientes', 'clients');
    }
};
