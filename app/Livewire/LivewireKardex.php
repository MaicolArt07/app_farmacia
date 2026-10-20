<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MovimientoInventario;

class LivewireKardex extends Component
{
    use WithPagination;

    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $query = MovimientoInventario::with(['product', 'lot', 'user']);

        if ($this->search) {
            $query->whereHas('product', function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%");
            })->orWhereHas('lot', function ($q) {
                $q->where('codigo_lote', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.kardex.index', [
            'movements' => $query->orderBy('id', 'desc')->paginate(15),
        ]);
    }
}