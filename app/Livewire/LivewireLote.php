<?php

namespace App\Livewire;

use App\Models\Lot;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireLot extends Component
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
        $query = Lot::with('product');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('batch_code', 'like', "%{$this->search}%")
                  ->orWhere('location', 'like', "%{$this->search}%")
                  ->orWhere('expiration_date', 'like', "%{$this->search}%")
                  ->orWhereHas('product', function ($sub) {
                      $sub->where('code', 'like', "%{$this->search}%")
                          ->orWhere('barcode', 'like', "%{$this->search}%")
                          ->orWhere('name', 'like', "%{$this->search}%")
                          ->orWhere('generic_name', 'like', "%{$this->search}%");
                  });
            });
        }

        return view('livewire.lot.index', [
            'lots' => $query->orderBy('expiration_date', 'asc')
                            ->orderBy('id', 'desc')
                            ->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $lot = Lot::findOrFail($id);
            $lot->state = $lot->state ? 0 : 1;
            $lot->save();

            $this->dispatch(
                'toast',
                type: $lot->state ? 'success' : 'warning',
                message: $lot->state
                    ? 'Lote activado correctamente.'
                    : 'Lote desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del lote.');
        }
    }
}
