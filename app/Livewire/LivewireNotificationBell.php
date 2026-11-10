<?php

namespace App\Livewire;

use App\Support\InventoryAlerts;
use Livewire\Component;

class LivewireNotificationBell extends Component
{
    public function render()
    {
        return view('livewire.notification-bell', InventoryAlerts::summary());
    }
}
