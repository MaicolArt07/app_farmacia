<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('sin_product_codes', 'codigos_producto_sin');
    }

    public function down(): void
    {
        Schema::rename('codigos_producto_sin', 'sin_product_codes');
    }
};
