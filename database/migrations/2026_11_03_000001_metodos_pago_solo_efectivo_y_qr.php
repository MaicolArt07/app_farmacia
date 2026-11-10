<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * La farmacia solo cobra en Efectivo o por QR.
 * - "Transferencia" (codigo_sin 7) pasa a llamarse "QR": el pago QR es una
 *   transferencia bancaria, así que conserva su código SIN.
 * - "Tarjeta" y "Mixto" se desactivan (no se borran, por si se reactivan).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('metodos_pago')->where('nombre', 'Transferencia')
            ->update(['nombre' => 'QR', 'estado' => 1, 'updated_at' => now()]);

        if (!DB::table('metodos_pago')->where('nombre', 'QR')->exists()) {
            DB::table('metodos_pago')->insert([
                'nombre' => 'QR', 'codigo_sin' => 7, 'estado' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        DB::table('metodos_pago')->whereIn('nombre', ['Tarjeta', 'Mixto'])
            ->update(['estado' => 0, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('metodos_pago')->where('nombre', 'QR')
            ->update(['nombre' => 'Transferencia', 'updated_at' => now()]);

        DB::table('metodos_pago')->whereIn('nombre', ['Tarjeta', 'Mixto'])
            ->update(['estado' => 1, 'updated_at' => now()]);
    }
};
