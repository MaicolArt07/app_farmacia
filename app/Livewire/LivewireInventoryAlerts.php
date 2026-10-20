<?php

namespace App\Livewire;

use App\Models\Lote;
use App\Models\Product;
use Livewire\Component;

class LivewireInventoryAlerts extends Component
{
    public function render()
    {
        $today = now()->toDateString();
        $limitDate = now()->addDays(30)->toDateString();

        return view('livewire.inventory-alerts.index', [
            'expiredLots' => Lote::with('product')
                ->whereDate('fecha_vencimiento', '<', $today)
                ->where('cantidad_disponible', '>', 0)
                ->orderBy('fecha_vencimiento')
                ->get(),

            'nearExpirationLots' => Lote::with('product')
                ->whereDate('fecha_vencimiento', '>=', $today)
                ->whereDate('fecha_vencimiento', '<=', $limitDate)
                ->where('cantidad_disponible', '>', 0)
                ->orderBy('fecha_vencimiento')
                ->get(),

            'lowStockProducts' => Product::with('lots')
                ->where('state', 1)
                ->get()
                ->filter(function ($product) {
                    $stock = $product->lots->where('estado', 1)->sum('cantidad_disponible');
                    return $stock <= $product->minimum_stock;
                }),
        ]);
    }
}