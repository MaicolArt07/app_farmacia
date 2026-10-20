<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AjusteInventario;

class LivewireAjusteInventario extends Component
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
        $query = AjusteInventario::with(['product', 'lot', 'user']);

        if ($this->search) {
            $query->whereHas('product', function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%");
            })->orWhereHas('lot', function ($q) {
                $q->where('codigo_lote', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.inventory-adjustment.index', [
            'adjustments' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }
}
