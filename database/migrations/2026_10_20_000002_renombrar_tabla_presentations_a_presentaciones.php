<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('presentations', 'presentaciones');

        Schema::table('presentaciones', function ($table) {
            $table->renameColumn('name', 'nombre');
            $table->renameColumn('description', 'descripcion');
            $table->renameColumn('state', 'estado');
        });
    }

    public function down(): void
    {
        Schema::table('presentaciones', function ($table) {
            $table->renameColumn('nombre', 'name');
            $table->renameColumn('descripcion', 'description');
            $table->renameColumn('estado', 'state');
        });

        Schema::rename('presentaciones', 'presentations');
    }
};
