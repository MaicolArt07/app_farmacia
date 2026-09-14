<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_person')->constrained('person')->restrictOnDelete();
            $table->string('company_name', 150);
            $table->string('contact_position', 100)->nullable();
            $table->string('nit', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('state')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
