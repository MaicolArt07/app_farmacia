<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_cash_register')->constrained('cash_registers')->cascadeOnDelete();
            $table->foreignId('id_user')->constrained('users')->restrictOnDelete();
            $table->enum('type', ['INCOME', 'EXPENSE']);
            $table->string('concept', 150);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('observation')->nullable();
            $table->boolean('state')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
