<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cierre de caja en dos verificaciones:
 *   1) QR: lo que el sistema registra por QR vs. lo que llegó a la cuenta.
 *   2) Efectivo: cortes contados vs. efectivo esperado (monto_cierre/diferencia).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            if (!Schema::hasColumn('cajas', 'monto_qr_esperado')) {
                $table->decimal('monto_qr_esperado', 10, 2)->nullable()->after('diferencia');
            }
            if (!Schema::hasColumn('cajas', 'monto_qr_verificado')) {
                $table->decimal('monto_qr_verificado', 10, 2)->nullable()->after('monto_qr_esperado');
            }
            if (!Schema::hasColumn('cajas', 'diferencia_qr')) {
                $table->decimal('diferencia_qr', 10, 2)->nullable()->after('monto_qr_verificado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropColumn(['monto_qr_esperado', 'monto_qr_verificado', 'diferencia_qr']);
        });
    }
};
