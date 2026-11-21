<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\Auth;

class LivewireKardex extends Component
{
    use WithPagination;

    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $query = MovimientoInventario::with(['product', 'lot', 'user']);

        $user = Auth::user();
        if ($user && ! $user->can('Super Admin')) {
            $query->whereHas('lot', fn ($q) => $q->where('id_sucursal', $user->id_sucursal));
        }

        if ($this->search) {
            // Agrupado para que el "o" no se salte el filtro de sucursal de arriba.
            $query->where(fn ($w) => $w
                ->whereHas('product', fn ($q) => $q->buscarNombre($this->search, ['code']))
                ->orWhereHas('lot', function ($q) {
                    $q->where('codigo_lote', 'like', "%{$this->search}%");
                }));
        }

        return view('livewire.kardex.index', [
            'movements' => $query->orderBy('id', 'desc')->paginate(15),
        ]);
    }
}