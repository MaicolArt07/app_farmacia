<?php

namespace App\Support;

use App\Models\Lote;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;

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
            $user = Auth::user();
            $sucursalId = $user?->id_sucursal;
            $scoped = $user && ! $user->can('Super Admin');

            $expiredCount = Lote::whereDate('fecha_vencimiento', '<', $today)
                ->where('cantidad_disponible', '>', 0)
                ->when($scoped, fn ($q) => $q->where('id_sucursal', $sucursalId))
                ->count();

            $nearExpirationCount = Lote::whereDate('fecha_vencimiento', '>=', $today)
                ->whereDate('fecha_vencimiento', '<=', $limitDate)
                ->where('cantidad_disponible', '>', 0)
                ->when($scoped, fn ($q) => $q->where('id_sucursal', $sucursalId))
                ->count();

            $lowStockProducts = Producto::with(['lots' => fn ($q) => $q->when($scoped, fn ($q2) => $q2->where('id_sucursal', $sucursalId))])
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

    /**
     * Las tres listas de alertas (con laboratorio), filtradas por nombre /
     * código. La usan la pantalla de Notificaciones y su exportación a Excel,
     * así ambas muestran exactamente lo mismo.
     *
     * @return array{expired: \Illuminate\Support\Collection, near: \Illuminate\Support\Collection, low: \Illuminate\Support\Collection}
     */
    public static function lists(string $term = ''): array
    {
        $term = trim($term);
        $today = now()->startOfDay();
        $limitDate = now()->addDays(30)->toDateString();
        $user = Auth::user();
        $sucursalId = $user?->id_sucursal;
        $scoped = $user && ! $user->can('Super Admin');

        $productFilter = function ($q) use ($term) {
            $q->where('nombre', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('barcode', 'like', "%{$term}%");
        };

        $lotQuery = fn () => Lote::with('product.laboratory')
            ->where('cantidad_disponible', '>', 0)
            ->when($scoped, fn ($q) => $q->where('id_sucursal', $sucursalId))
            ->when($term !== '', fn ($q) => $q->whereHas('product', $productFilter));

        // Vencidos: el que venció hace más tiempo primero.
        $expired = $lotQuery()
            ->whereDate('fecha_vencimiento', '<', $today->toDateString())
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(function ($lot) use ($today) {
                $lot->days = (int) $lot->fecha_vencimiento->copy()->startOfDay()->diffInDays($today);
                return $lot;
            });

        // Por vencer: el más próximo a vencer primero.
        $near = $lotQuery()
            ->whereDate('fecha_vencimiento', '>=', $today->toDateString())
            ->whereDate('fecha_vencimiento', '<=', $limitDate)
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(function ($lot) use ($today) {
                $lot->days = (int) $today->diffInDays($lot->fecha_vencimiento->copy()->startOfDay());
                return $lot;
            });

        // Stock bajo: agotados primero, luego menor stock, luego por nombre.
        $low = Producto::with(['lots' => fn ($q) => $q->when($scoped, fn ($q2) => $q2->where('id_sucursal', $sucursalId)), 'laboratory'])
            ->where('estado', 1)
            ->when($term !== '', fn ($q) => $q->where($productFilter))
            ->get()
            ->map(function ($product) {
                $product->stock_actual = (int) $product->lots->where('estado', 1)->sum('cantidad_disponible');
                return $product;
            })
            ->filter(fn ($product) => $product->stock_actual <= $product->stock_minimo)
            ->sortBy([['stock_actual', 'asc'], ['nombre', 'asc']])
            ->values();

        return ['expired' => $expired, 'near' => $near, 'low' => $low];
    }
}
