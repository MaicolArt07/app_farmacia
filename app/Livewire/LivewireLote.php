<?php

namespace App\Livewire;

use App\Models\Lote;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireLote extends Component
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
        $query = Lote::with('product');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('codigo_lote', 'like', "%{$this->search}%")
                  ->orWhere('ubicacion', 'like', "%{$this->search}%")
                  ->orWhere('fecha_vencimiento', 'like', "%{$this->search}%")
                  ->orWhereHas('product', function ($sub) {
                      $sub->where('code', 'like', "%{$this->search}%")
                          ->orWhere('barcode', 'like', "%{$this->search}%")
                          ->orWhere('name', 'like', "%{$this->search}%")
                          ->orWhere('generic_name', 'like', "%{$this->search}%");
                  });
            });
        }

        return view('livewire.lote.index', [
            'lots' => $query->orderBy('fecha_vencimiento', 'asc')
                            ->orderBy('id', 'desc')
                            ->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $lot = Lote::findOrFail($id);
            $lot->estado = $lot->estado ? 0 : 1;
            $lot->save();

            $this->dispatch(
                'toast',
                type: $lot->estado ? 'success' : 'warning',
                message: $lot->estado
                    ? 'Lote activado correctamente.'
                    : 'Lote desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del lote.');
        }
    }
}
