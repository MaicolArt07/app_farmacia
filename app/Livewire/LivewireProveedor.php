<?php

namespace App\Livewire;

use App\Models\Proveedor;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireProveedor extends Component
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
        $query = Proveedor::with('person');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('razon_social', 'like', "%{$this->search}%")
                  ->orWhere('cargo_contacto', 'like', "%{$this->search}%")
                  ->orWhere('nit', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhereHas('person', function ($personQuery) {
                      $personQuery->where('name', 'like', "%{$this->search}%")
                          ->orWhere('lastname', 'like', "%{$this->search}%")
                          ->orWhere('ci', 'like', "%{$this->search}%");
                  });
            });
        }

        return view('livewire.proveedor.index', [
            'suppliers' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $supplier = Proveedor::findOrFail($id);
            $supplier->estado = $supplier->estado ? 0 : 1;
            $supplier->save();

            $this->dispatch(
                'toast',
                type: $supplier->estado ? 'success' : 'warning',
                message: $supplier->estado
                    ? 'Proveedor activado correctamente.'
                    : 'Proveedor desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del proveedor.');
        }
    }
}
