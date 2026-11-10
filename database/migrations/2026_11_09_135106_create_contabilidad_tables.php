<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos_comunes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('nombre', 150);
            $table->string('descripcion', 255)->nullable();
            $table->decimal('monto_referencia', 10, 2)->nullable();
            $table->unsignedTinyInteger('estado')->default(1);
            $table->timestamps();
        });

        Schema::create('movimientos_contables', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->date('fecha');
            $table->string('tipo', 10); // DEBE | HABER
            $table->string('concepto', 255);
            $table->decimal('monto', 12, 2);
            $table->string('origen', 30); // COMPRA | CIERRE_CAJA | GASTO_COMUN | MANUAL
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('id_gasto_comun')->nullable()->constrained('gastos_comunes')->nullOnDelete();
            $table->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->string('observacion', 255)->nullable();
            $table->timestamps();

            $table->index(['origen', 'reference_id']);
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_contables');
        Schema::dropIfExists('gastos_comunes');
    }
};
