<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('person', 'personas');

        Schema::table('personas', function ($table) {
            $table->renameColumn('name', 'nombre');
            $table->renameColumn('lastname', 'apellido');
            $table->renameColumn('phone', 'telefono');
            $table->renameColumn('address', 'direccion');
            $table->renameColumn('state', 'estado');
        });
    }

    public function down(): void
    {
        Schema::table('personas', function ($table) {
            $table->renameColumn('nombre', 'name');
            $table->renameColumn('apellido', 'lastname');
            $table->renameColumn('telefono', 'phone');
            $table->renameColumn('direccion', 'address');
            $table->renameColumn('estado', 'state');
        });

        Schema::rename('personas', 'person');
    }
};
