<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('name', 100);
            $table->unsignedInteger('codigo_sin')->nullable();
            $table->boolean('state')->default(1);
            $table->timestamps();
        });

        // Códigos oficiales reales del catálogo "Tipo de Método de Pago" del SIN
        // (ver memoria: facturacion_siat_integracion.md).
        DB::table('payment_methods')->insert([
            ['name' => 'Efectivo', 'codigo_sin' => 1, 'state' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tarjeta', 'codigo_sin' => 2, 'state' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Transferencia', 'codigo_sin' => 7, 'state' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mixto', 'codigo_sin' => 10, 'state' => 1, 'created_at' => now(), 'updated_at' => now()], // Efectivo-Tarjeta
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
