<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_client')->nullable()->constrained('clients')->restrictOnDelete();
            $table->foreignId('id_user')->constrained('users')->restrictOnDelete();
            $table->dateTime('sale_date');
            $table->string('payment_method', 50)->default('efectivo');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('observation')->nullable();
            $table->boolean('state')->default(1);
            $table->enum('status', ['ACTIVE', 'CANCELLED'])->default('ACTIVE');
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();

            $table->index('sale_date');
            $table->index('payment_method');
            $table->index('state');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
