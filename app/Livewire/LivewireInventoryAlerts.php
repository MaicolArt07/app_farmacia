<?php

namespace App\Livewire;

use App\Models\Lot;
use App\Models\Product;
use Livewire\Component;

class LivewireInventoryAlerts extends Component
{
    public function render()
    {
        $today = now()->toDateString();
        $limitDate = now()->addDays(30)->toDateString();

        return view('livewire.inventory-alerts.index', [
            'expiredLots' => Lot::with('product')
                ->whereDate('expiration_date', '<', $today)
                ->where('quantity_available', '>', 0)
                ->orderBy('expiration_date')
                ->get(),

            'nearExpirationLots' => Lot::with('product')
                ->whereDate('expiration_date', '>=', $today)
                ->whereDate('expiration_date', '<=', $limitDate)
                ->where('quantity_available', '>', 0)
                ->orderBy('expiration_date')
                ->get(),

            'lowStockProducts' => Product::with('lots')
                ->where('state', 1)
                ->get()
                ->filter(function ($product) {
                    $stock = $product->lots->where('state', 1)->sum('quantity_available');
                    return $stock <= $product->minimum_stock;
                }),
        ]);
    }
}