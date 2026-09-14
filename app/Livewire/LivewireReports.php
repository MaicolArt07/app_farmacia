<?php

namespace App\Livewire;

use Livewire\Component;

class LivewireReports extends Component
{
    public function render()
    {
        return view('livewire.reports.index', [
            'defaultFrom' => now()->startOfMonth()->toDateString(),
            'defaultTo' => now()->toDateString(),
        ]);
    }
}
