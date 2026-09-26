<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->string('validity_period', 20)->default('DAILY')->after('opening_amount');
            $table->timestamp('expires_at')->nullable()->after('validity_period');
        });

        DB::table('cash_registers')->where('status', 'OPEN')->orderBy('id')->each(function ($cash) {
            $opened = $cash->opened_at ?? $cash->created_at ?? now();
            DB::table('cash_registers')->where('id', $cash->id)->update([
                'expires_at' => \Illuminate\Support\Carbon::parse($opened)->addDay(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropColumn(['validity_period', 'expires_at']);
        });
    }
};
