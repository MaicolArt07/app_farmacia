<?php

namespace App\Livewire;

use App\Models\Lot;
use App\Models\Product;
use Livewire\Component;

class LivewireNotificationBell extends Component
{
    public function render()
    {
        $today = now()->toDateString();
        $limitDate = now()->addDays(30)->toDateString();

        $expiredCount = Lot::whereDate('expiration_date', '<', $today)
            ->where('quantity_available', '>', 0)
            ->count();

        $nearExpirationCount = Lot::whereDate('expiration_date', '>=', $today)
            ->whereDate('expiration_date', '<=', $limitDate)
            ->where('quantity_available', '>', 0)
            ->count();

        $lowStockProducts = Product::with('lots')
            ->where('state', 1)
            ->get()
            ->filter(function ($product) {
                $stock = $product->lots->where('state', 1)->sum('quantity_available');
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
