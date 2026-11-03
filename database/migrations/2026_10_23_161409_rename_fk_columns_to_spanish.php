<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * [tabla, columna_vieja, columna_nueva]
     */
    private array $renames = [
        ['users', 'id_person', 'id_persona'],
        ['clientes', 'id_person', 'id_persona'],
        ['proveedores', 'id_person', 'id_persona'],
        ['personas', 'id_country', 'id_pais'],
        ['productos', 'id_category', 'id_categoria'],
        ['productos', 'id_laboratory', 'id_laboratorio'],
        ['productos', 'id_presentation', 'id_presentacion'],
        ['productos', 'id_sin_product_code', 'id_codigo_producto_sin'],
        ['lotes', 'id_product', 'id_producto'],
        ['movimientos_inventario', 'id_product', 'id_producto'],
        ['movimientos_inventario', 'id_lot', 'id_lote'],
        ['movimientos_inventario', 'id_user', 'id_usuario'],
        ['ajustes_inventario', 'id_product', 'id_producto'],
        ['ajustes_inventario', 'id_lot', 'id_lote'],
        ['ajustes_inventario', 'id_user', 'id_usuario'],
        ['compras', 'id_supplier', 'id_proveedor'],
        ['compras', 'id_user', 'id_usuario'],
        ['detalles_compra', 'id_purchase', 'id_compra'],
        ['detalles_compra', 'id_product', 'id_producto'],
        ['cajas', 'id_user', 'id_usuario'],
        ['movimientos_caja', 'id_cash_register', 'id_caja'],
        ['movimientos_caja', 'id_user', 'id_usuario'],
        ['ventas', 'id_client', 'id_cliente'],
        ['ventas', 'id_user', 'id_usuario'],
        ['ventas', 'id_cash_register', 'id_caja'],
        ['ventas', 'id_payment_method', 'id_metodo_pago'],
        ['detalles_venta', 'id_sale', 'id_venta'],
        ['detalles_venta', 'id_product', 'id_producto'],
        ['detalles_venta', 'id_lot', 'id_lote'],
        ['facturas', 'id_sale', 'id_venta'],
        ['facturas', 'id_user', 'id_usuario'],
        ['sucursales', 'id_company', 'id_empresa'],
        ['login_logs', 'id_user', 'id_usuario'],
    ];

    public function up(): void
    {
        foreach ($this->renames as [$table, $old, $new]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $old) && ! Schema::hasColumn($table, $new)) {
                Schema::table($table, function (Blueprint $blueprint) use ($old, $new) {
                    $blueprint->renameColumn($old, $new);
                });
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->renames) as [$table, $old, $new]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $new) && ! Schema::hasColumn($table, $old)) {
                Schema::table($table, function (Blueprint $blueprint) use ($old, $new) {
                    $blueprint->renameColumn($new, $old);
                });
            }
        }
    }
};
