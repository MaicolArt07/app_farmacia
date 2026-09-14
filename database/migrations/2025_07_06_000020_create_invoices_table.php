<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_sale')->unique()->constrained('sales')->restrictOnDelete();
            $table->foreignId('id_user')->constrained('users')->restrictOnDelete();
            $table->enum('modo', ['SIMULADO', 'REAL'])->default('SIMULADO');
            $table->enum('ambiente', ['PRUEBA', 'PRODUCCION'])->default('PRUEBA');
            $table->string('tipo_documento_identidad', 20)->nullable();
            $table->string('nit_cliente', 30)->nullable();
            $table->string('razon_social', 150);
            $table->unsignedBigInteger('numero_factura')->nullable();
            $table->string('cuf', 100)->nullable()->unique();
            $table->string('cuis', 50)->nullable();
            $table->string('cufd', 50)->nullable();
            $table->string('codigo_control', 50)->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('estado', ['PENDIENTE', 'ENVIADA', 'OBSERVADA', 'ERROR', 'ANULADA'])->default('PENDIENTE');
            $table->text('observaciones')->nullable();
            $table->longText('raw_request')->nullable();
            $table->longText('raw_response')->nullable();
            $table->timestamp('anulado_at')->nullable();
            $table->text('anulado_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
