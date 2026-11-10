<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Productos que vienen en paquete y también se venden sueltos
 * (ej. PAÑAL HUGGIES RN PAQUETE X20 -> PAÑAL HUGGIES RN UNIDAD).
 *
 * - id_producto_unidad: producto "unidad" en el que se abre este paquete.
 * - unidades_por_paquete: cuántas unidades salen al abrir un paquete.
 *
 * Al "Abrir paquete" se descuenta 1 del paquete y se crea un lote de
 * N unidades en el producto unidad (ver App\Services\AperturaPaquete).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_producto_unidad')->nullable()->after('id_codigo_producto_sin');
            $table->unsignedInteger('unidades_por_paquete')->nullable()->after('id_producto_unidad');

            $table->foreign('id_producto_unidad', 'fk_productos_producto_unidad')
                ->references('id')->on('productos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign('fk_productos_producto_unidad');
            $table->dropColumn(['id_producto_unidad', 'unidades_por_paquete']);
        });
    }
};
