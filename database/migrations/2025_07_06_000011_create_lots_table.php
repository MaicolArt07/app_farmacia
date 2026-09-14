<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_product')->constrained('products')->restrictOnDelete();
            $table->string('batch_code', 100);
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->integer('quantity_in')->default(0);
            $table->integer('quantity_available')->default(0);
            $table->date('expiration_date')->nullable();
            $table->string('location', 100)->nullable();
            $table->boolean('state')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lots');
    }
};
