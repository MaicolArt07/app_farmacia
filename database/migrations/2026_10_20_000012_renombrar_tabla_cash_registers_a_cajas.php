<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('cash_registers', 'cajas');

        Schema::table('cajas', function ($table) {
            $table->renameColumn('opening_date', 'fecha_apertura');
            $table->renameColumn('opening_amount', 'monto_apertura');
            $table->renameColumn('closing_amount', 'monto_cierre');
            $table->renameColumn('expected_amount', 'monto_esperado');
            $table->renameColumn('difference', 'diferencia');
            $table->renameColumn('validity_period', 'periodo_validez');
            $table->renameColumn('observation', 'observacion');
            $table->renameColumn('state', 'estado');
        });

        Schema::rename('cash_movements', 'movimientos_caja');

        Schema::table('movimientos_caja', function ($table) {
            $table->renameColumn('concept', 'concepto');
            $table->renameColumn('amount', 'monto');
            $table->renameColumn('observation', 'observacion');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_caja', function ($table) {
            $table->renameColumn('concepto', 'concept');
            $table->renameColumn('monto', 'amount');
            $table->renameColumn('observacion', 'observation');
        });

        Schema::rename('movimientos_caja', 'cash_movements');

        Schema::table('cajas', function ($table) {
            $table->renameColumn('fecha_apertura', 'opening_date');
            $table->renameColumn('monto_apertura', 'opening_amount');
            $table->renameColumn('monto_cierre', 'closing_amount');
            $table->renameColumn('monto_esperado', 'expected_amount');
            $table->renameColumn('diferencia', 'difference');
            $table->renameColumn('periodo_validez', 'validity_period');
            $table->renameColumn('observacion', 'observation');
            $table->renameColumn('estado', 'state');
        });

        Schema::rename('cajas', 'cash_registers');
    }
};
