<?php

namespace App\Livewire;

use App\Models\Lote;
use App\Models\Producto;
use Livewire\Component;

class LivewireInventoryAlerts extends Component
{
    public $search = '';

    public function render()
    {
        $today = now()->startOfDay();
        $limitDate = now()->addDays(30)->toDateString();
        $term = trim($this->search);

        // Filtro por nombre/código del producto (aplica a las tres secciones).
        $productFilter = function ($q) use ($term) {
            $q->where('nombre', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('barcode', 'like', "%{$term}%");
        };

        $lotQuery = fn () => Lote::with('product.laboratory')
            ->where('cantidad_disponible', '>', 0)
            ->when($term !== '', fn ($q) => $q->whereHas('product', $productFilter));

        // Vencidos: el que venció hace más tiempo primero.
        $expiredLots = $lotQuery()
            ->whereDate('fecha_vencimiento', '<', $today->toDateString())
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(function ($lot) use ($today) {
                $lot->days = (int) $lot->fecha_vencimiento->copy()->startOfDay()->diffInDays($today);
                return $lot;
            });

        // Por vencer: el más próximo a vencer primero.
        $nearExpirationLots = $lotQuery()
            ->whereDate('fecha_vencimiento', '>=', $today->toDateString())
            ->whereDate('fecha_vencimiento', '<=', $limitDate)
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(function ($lot) use ($today) {
                $lot->days = (int) $today->diffInDays($lot->fecha_vencimiento->copy()->startOfDay());
                return $lot;
            });

        // Stock bajo: agotados primero, luego menor stock, luego por nombre.
        $lowStockProducts = Producto::with(['lots', 'laboratory'])
            ->where('estado', 1)
            ->when($term !== '', fn ($q) => $q->where($productFilter))
            ->get()
            ->map(function ($product) {
                $product->stock_actual = (int) $product->lots->where('estado', 1)->sum('cantidad_disponible');
                return $product;
            })
            ->filter(fn ($product) => $product->stock_actual <= $product->stock_minimo)
            ->sortBy([
                ['stock_actual', 'asc'],
                ['nombre', 'asc'],
            ])
            ->values();

        return view('livewire.alertas-inventario.index', [
            'expiredLots' => $expiredLots,
            'nearExpirationLots' => $nearExpirationLots,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
