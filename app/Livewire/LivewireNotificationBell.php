<?php

namespace App\Livewire;

use App\Models\Lote;
use App\Models\Product;
use Livewire\Component;

class LivewireNotificationBell extends Component
{
    public function render()
    {
        $today = now()->toDateString();
        $limitDate = now()->addDays(30)->toDateString();

        $expiredCount = Lote::whereDate('fecha_vencimiento', '<', $today)
            ->where('cantidad_disponible', '>', 0)
            ->count();

        $nearExpirationCount = Lote::whereDate('fecha_vencimiento', '>=', $today)
            ->whereDate('fecha_vencimiento', '<=', $limitDate)
            ->where('cantidad_disponible', '>', 0)
            ->count();

        $lowStockProducts = Product::with('lots')
            ->where('state', 1)
            ->get()
            ->filter(function ($product) {
                $stock = $product->lots->where('estado', 1)->sum('cantidad_disponible');
                return $stock <= $product->minimum_stock;
            });

        $lowStockCount = $lowStockProducts->count();
        $total = $expiredCount + $nearExpirationCount + $lowStockCount;

        return view('livewire.notification-bell', [
            'expiredCount' => $expiredCount,
            'nearExpirationCount' => $nearExpirationCount,
            'lowStockCount' => $lowStockCount,
            'lowStockProducts' => $lowStockProducts->take(5),
            'total' => $total,
        ]);
    }
}
