<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sin_product_codes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('codigo_actividad', 20);
            $table->unsignedBigInteger('codigo_producto');
            $table->string('descripcion', 255);
            $table->json('nandina')->nullable();
            $table->timestamps();

            $table->unique(['codigo_actividad', 'codigo_producto']);
            $table->index('descripcion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sin_product_codes');
    }
};
