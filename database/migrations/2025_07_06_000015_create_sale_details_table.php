<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_sale')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('id_product')->constrained('products')->restrictOnDelete();
            $table->foreignId('id_lot')->constrained('lots')->restrictOnDelete();
            $table->integer('quantity')->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_details');
    }
};
