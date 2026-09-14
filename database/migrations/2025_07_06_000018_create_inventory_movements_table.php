<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_product')->constrained('products')->restrictOnDelete();
            $table->foreignId('id_lot')->nullable()->constrained('lots')->nullOnDelete();
            $table->foreignId('id_user')->constrained('users')->restrictOnDelete();
            $table->enum('movement_type', ['IN', 'OUT', 'RETURN', 'ADJUSTMENT', 'CANCEL']);
            $table->string('reference_type', 50);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->integer('quantity');
            $table->integer('stock_before')->default(0);
            $table->integer('stock_after')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
