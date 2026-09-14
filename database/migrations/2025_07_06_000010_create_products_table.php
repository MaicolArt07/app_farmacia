<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_category')->constrained('categories')->restrictOnDelete();
            $table->foreignId('id_laboratory')->nullable()->constrained('laboratories')->restrictOnDelete();
            $table->foreignId('id_presentation')->constrained('presentations')->restrictOnDelete();
            $table->foreignId('id_brand')->nullable()->constrained('brands')->restrictOnDelete();
            $table->string('code', 50)->unique();
            $table->string('barcode', 100)->nullable();
            $table->string('name', 150);
            $table->string('generic_name', 150)->nullable();
            $table->string('concentration', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->boolean('requires_prescription')->default(false);
            $table->boolean('state')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
