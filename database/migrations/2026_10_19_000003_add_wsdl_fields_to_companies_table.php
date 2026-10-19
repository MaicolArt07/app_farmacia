<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedInteger('numero_solicitud')->nullable()->after('codigo_sistema');
            $table->string('soap_version', 5)->default('V2')->after('ambiente'); // V2 | V3 (sfd)
            $table->string('wsdl_sincronizacion', 255)->nullable();
            $table->string('wsdl_operaciones', 255)->nullable();
            $table->string('wsdl_codigos', 255)->nullable();
            $table->string('wsdl_factura_compra_venta', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'numero_solicitud',
                'soap_version',
                'wsdl_sincronizacion',
                'wsdl_operaciones',
                'wsdl_codigos',
                'wsdl_factura_compra_venta',
            ]);
        });
    }
};
