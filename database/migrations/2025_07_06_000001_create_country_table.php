<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->tinyInteger('state')->default(1);
            $table->timestamps();
        });

        DB::table('country')->insert([
            ['name' => 'Bolivia', 'state' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('country');
    }
};
