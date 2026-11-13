<?php

namespace App\Livewire;

use App\Support\InventoryAlerts;
use Livewire\Component;

class LivewireInventoryAlerts extends Component
{
    public $search = '';

    public function render()
    {
        // Mismas listas que la exportación a Excel (App\Support\InventoryAlerts).
        $lists = InventoryAlerts::lists((string) $this->search);

        return view('livewire.alertas-inventario.index', [
            'expiredLots' => $lists['expired'],
            'nearExpirationLots' => $lists['near'],
            'lowStockProducts' => $lists['low'],
        ]);
    }
}
