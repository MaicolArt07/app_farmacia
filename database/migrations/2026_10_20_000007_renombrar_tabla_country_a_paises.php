<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('country', 'paises');

        Schema::table('paises', function ($table) {
            $table->renameColumn('name', 'nombre');
            $table->renameColumn('state', 'estado');
        });
    }

    public function down(): void
    {
        Schema::table('paises', function ($table) {
            $table->renameColumn('nombre', 'name');
            $table->renameColumn('estado', 'state');
        });

        Schema::rename('paises', 'country');
    }
};
