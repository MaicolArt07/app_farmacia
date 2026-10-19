<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('id_company')->constrained('companies')->restrictOnDelete();
            $table->unsignedInteger('numero_sucursal')->default(0); // 0 = casa matriz, asignado por el SIN
            $table->unsignedInteger('punto_venta')->default(0); // asignado por el SIN
            $table->string('name', 150);
            $table->string('address', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->boolean('state')->default(1);
            $table->timestamps();

            $table->unique(['id_company', 'numero_sucursal', 'punto_venta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
