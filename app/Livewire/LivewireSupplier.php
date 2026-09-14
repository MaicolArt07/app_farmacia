<?php

namespace App\Livewire;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireSupplier extends Component
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
        $query = Supplier::with('person');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('company_name', 'like', "%{$this->search}%")
                  ->orWhere('contact_position', 'like', "%{$this->search}%")
                  ->orWhere('nit', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhereHas('person', function ($personQuery) {
                      $personQuery->where('name', 'like', "%{$this->search}%")
                          ->orWhere('lastname', 'like', "%{$this->search}%")
                          ->orWhere('ci', 'like', "%{$this->search}%");
                  });
            });
        }

        return view('livewire.supplier.index', [
            'suppliers' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            $supplier->state = $supplier->state ? 0 : 1;
            $supplier->save();

            $this->dispatch(
                'toast',
                type: $supplier->state ? 'success' : 'warning',
                message: $supplier->state
                    ? 'Proveedor activado correctamente.'
                    : 'Proveedor desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del proveedor.');
        }
    }
}
