<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('id_cash_register')->nullable()->after('id_user')
                ->constrained('cash_registers')->nullOnDelete();
        });

        // Vincula ventas existentes con la caja abierta de su usuario en ese momento, como mejor esfuerzo.
        $sales = DB::table('sales')->whereNull('id_cash_register')->get();

        foreach ($sales as $sale) {
            $cash = DB::table('cash_registers')
                ->where('id_user', $sale->id_user)
                ->where('opening_date', '<=', $sale->sale_date)
                ->orderByDesc('id')
                ->first();

            if ($cash) {
                DB::table('sales')->where('id', $sale->id)->update(['id_cash_register' => $cash->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_cash_register');
        });
    }
};
