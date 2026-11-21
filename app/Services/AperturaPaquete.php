<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * "Abrir paquete": convierte 1 paquete cerrado en N unidades sueltas.
 *
 * - Descuenta 1 del lote más antiguo del producto PAQUETE (movimiento OUT).
 * - Crea un lote nuevo en el producto UNIDAD con N unidades (movimiento IN).
 *   Costo por unidad = costo del paquete / N. Precio = precio de venta de la unidad.
 * - Ambos movimientos quedan en el Kardex con reference_type PACKAGE_OPEN.
 *
 * No es una venta: no toca la caja.
 */
class AperturaPaquete
{
    public function abrir(Producto $pack, int $userId): Lote
    {
        if (!$pack->isPack()) {
            throw new RuntimeException('Este producto no está configurado como paquete.');
        }

        return DB::transaction(function () use ($pack, $userId) {
            $unit = Producto::whereKey($pack->id_producto_unidad)->lockForUpdate()->firstOrFail();
            $factor = (int) $pack->unidades_por_paquete;
            // El paquete se abre en la sucursal del usuario: sale de su stock y las unidades quedan ahí.
            $sucursalId = User::whereKey($userId)->value('id_sucursal');

            // Paquete más antiguo con stock (primero en entrar, primero en abrirse).
            $packLot = Lote::where('id_producto', $pack->id)
                ->where('id_sucursal', $sucursalId)
                ->where('estado', 1)
                ->where('cantidad_disponible', '>', 0)
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (!$packLot) {
                throw new RuntimeException('No quedan paquetes cerrados de ' . $pack->nombre_completo . '.');
            }

            $packBefore = (int) $packLot->cantidad_disponible;
            $packLot->cantidad_disponible -= 1;
            $packLot->save();

            $momento = now();
            $unitLot = Lote::create([
                'id_producto' => $unit->id,
                'id_sucursal' => $packLot->id_sucursal,
                'codigo_lote' => 'APERTURA-' . $packLot->codigo_lote . '-' . $momento->format('YmdHis'),
                'precio_compra' => round((float) $packLot->precio_compra / $factor, 2),
                'precio_venta' => $unit->precio_venta,
                'cantidad_ingresada' => $factor,
                'cantidad_disponible' => $factor,
                'fecha_vencimiento' => $packLot->fecha_vencimiento,
                'estado' => 1,
            ]);

            MovimientoInventario::create([
                'id_producto' => $pack->id,
                'id_lote' => $packLot->id,
                'id_usuario' => $userId,
                'tipo_movimiento' => 'OUT',
                'reference_type' => 'PACKAGE_OPEN',
                'reference_id' => $unitLot->id,
                'quantity' => 1,
                'stock_before' => $packBefore,
                'stock_after' => $packLot->cantidad_disponible,
                'descripcion' => "Apertura de paquete: 1 paquete abierto en {$factor} unidades de {$unit->nombre_completo}.",
            ]);

            MovimientoInventario::create([
                'id_producto' => $unit->id,
                'id_lote' => $unitLot->id,
                'id_usuario' => $userId,
                'tipo_movimiento' => 'IN',
                'reference_type' => 'PACKAGE_OPEN',
                'reference_id' => $packLot->id,
                'quantity' => $factor,
                'stock_before' => 0,
                'stock_after' => $factor,
                'descripcion' => "Apertura de paquete: ingreso de {$factor} unidades sueltas desde {$pack->nombre_completo}.",
            ]);

            return $unitLot;
        });
    }
}
