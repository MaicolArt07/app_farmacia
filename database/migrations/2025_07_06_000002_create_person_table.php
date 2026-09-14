<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_country')->constrained('country')->restrictOnDelete();
            $table->string('name', 50);
            $table->string('lastname', 50);
            $table->string('ci', 20);
            $table->string('phone', 20);
            $table->text('address');
            $table->tinyInteger('state')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person');
    }
};
