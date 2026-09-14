<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\InventoryAdjustment;

class LivewireInventoryAdjustment extends Component
{
    use WithPagination;

    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = InventoryAdjustment::with(['product', 'lot', 'user']);

        if ($this->search) {
            $query->whereHas('product', function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%");
            })->orWhereHas('lot', function ($q) {
                $q->where('batch_code', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.inventory-adjustment.index', [
            'adjustments' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }
}
