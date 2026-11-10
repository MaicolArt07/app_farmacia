<?php

namespace App\Support;

use App\Models\Lote;
use App\Models\Producto;

/**
 * Conteo de alertas de inventario (lotes vencidos, por vencer en 30 días y
 * productos con stock bajo). Lo usan la campana de notificaciones y el ítem
 * "Notificaciones" del menú, para que siempre muestren el mismo número.
 * Se calcula una sola vez por petición (once()).
 */
class InventoryAlerts
{
    public static function summary(): array
    {
        return once(function () {
            $today = now()->toDateString();
            $limitDate = now()->addDays(30)->toDateString();

            $expiredCount = Lote::whereDate('fecha_vencimiento', '<', $today)
                ->where('cantidad_disponible', '>', 0)
                ->count();

            $nearExpirationCount = Lote::whereDate('fecha_vencimiento', '>=', $today)
                ->whereDate('fecha_vencimiento', '<=', $limitDate)
                ->where('cantidad_disponible', '>', 0)
                ->count();

            $lowStockProducts = Producto::with('lots')
                ->where('estado', 1)
                ->get()
                ->filter(function ($product) {
                    $stock = $product->lots->where('estado', 1)->sum('cantidad_disponible');
                    return $stock <= $product->stock_minimo;
                });

            return [
                'expiredCount' => $expiredCount,
                'nearExpirationCount' => $nearExpirationCount,
                'lowStockCount' => $lowStockProducts->count(),
                'lowStockProducts' => $lowStockProducts->take(5),
                'total' => $expiredCount + $nearExpirationCount + $lowStockProducts->count(),
            ];
        });
    }

    public static function total(): int
    {
        return self::summary()['total'];
    }
}
