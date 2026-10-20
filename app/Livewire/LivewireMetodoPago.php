<?php

namespace App\Livewire;

use App\Models\MetodoPago;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireMetodoPago extends Component
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
        $query = MetodoPago::query();

        if (!empty($this->search)) {
            $query->where('nombre', 'like', "%{$this->search}%");
        }

        return view('livewire.metodo-pago.index', [
            'paymentMethods' => $query->orderBy('nombre')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $paymentMethod = MetodoPago::findOrFail($id);
            $paymentMethod->estado = $paymentMethod->estado ? 0 : 1;
            $paymentMethod->save();

            $this->dispatch(
                'toast',
                type: $paymentMethod->estado ? 'success' : 'warning',
                message: $paymentMethod->estado
                    ? 'Método de pago activado correctamente.'
                    : 'Método de pago desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del método de pago.');
        }
    }
}
