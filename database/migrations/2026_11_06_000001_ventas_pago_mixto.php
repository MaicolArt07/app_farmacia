<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pago MIXTO (parte en efectivo + parte por QR).
 * - ventas.monto_efectivo / ventas.monto_qr: cuánto de la venta fue en
 *   efectivo (va al cajón) y cuánto por QR (va a la cuenta).
 * - Se rellenan las ventas existentes: Efectivo => todo efectivo; QR => todo QR.
 * - Se reactiva el método "Mixto" (Tarjeta sigue desactivado).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas', 'monto_efectivo')) {
                $table->decimal('monto_efectivo', 12, 2)->nullable()->after('total');
            }
            if (!Schema::hasColumn('ventas', 'monto_qr')) {
                $table->decimal('monto_qr', 12, 2)->nullable()->after('monto_efectivo');
            }
        });

        DB::table('ventas')->whereRaw('UPPER(metodo_pago) = ?', ['EFECTIVO'])
            ->update(['monto_efectivo' => DB::raw('total'), 'monto_qr' => 0]);
        DB::table('ventas')->whereRaw('UPPER(metodo_pago) = ?', ['QR'])
            ->update(['monto_efectivo' => 0, 'monto_qr' => DB::raw('total')]);

        DB::table('metodos_pago')->where('nombre', 'Mixto')
            ->update(['estado' => 1, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('metodos_pago')->where('nombre', 'Mixto')
            ->update(['estado' => 0, 'updated_at' => now()]);

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['monto_efectivo', 'monto_qr']);
        });
    }
};
