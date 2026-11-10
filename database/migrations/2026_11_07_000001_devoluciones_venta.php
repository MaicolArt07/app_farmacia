<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Devoluciones parciales de productos de una venta (solo admin).
 * La venta queda con su total NETO (lo devuelto se descuenta) y se guarda
 * el historial en devoluciones / devolucion_detalles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_venta');
            $table->unsignedBigInteger('id_caja')->nullable();
            $table->unsignedBigInteger('id_usuario');
            $table->decimal('monto', 12, 2);
            $table->decimal('monto_efectivo', 12, 2)->default(0);
            $table->decimal('monto_qr', 12, 2)->default(0);
            $table->text('motivo');
            $table->timestamps();

            $table->foreign('id_venta', 'fk_devoluciones_venta')->references('id')->on('ventas');
            $table->foreign('id_caja', 'fk_devoluciones_caja')->references('id')->on('cajas')->nullOnDelete();
            $table->foreign('id_usuario', 'fk_devoluciones_usuario')->references('id')->on('users');
        });

        Schema::create('devolucion_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_devolucion');
            $table->unsignedBigInteger('id_detalle_venta');
            $table->unsignedBigInteger('id_producto')->nullable();
            $table->unsignedBigInteger('id_servicio')->nullable();
            $table->unsignedBigInteger('id_lote')->nullable();
            $table->integer('cantidad');
            $table->decimal('precio_venta', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->foreign('id_devolucion', 'fk_devdet_devolucion')->references('id')->on('devoluciones')->cascadeOnDelete();
            $table->foreign('id_detalle_venta', 'fk_devdet_detalle')->references('id')->on('detalles_venta');
        });

        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->integer('cantidad_devuelta')->default(0)->after('quantity');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('monto_devuelto', 12, 2)->default(0)->after('monto_qr');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', fn (Blueprint $t) => $t->dropColumn('monto_devuelto'));
        Schema::table('detalles_venta', fn (Blueprint $t) => $t->dropColumn('cantidad_devuelta'));
        Schema::dropIfExists('devolucion_detalles');
        Schema::dropIfExists('devoluciones');
    }
};
