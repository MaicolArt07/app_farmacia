<?php

namespace App\Livewire;

use App\Models\Sucursal;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireSucursal extends Component
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
        $query = Sucursal::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('direccion', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.branch.index', [
            'branches' => $query->orderBy('numero_sucursal')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $branch = Sucursal::findOrFail($id);
            $branch->estado = $branch->estado ? 0 : 1;
            $branch->save();

            $this->dispatch(
                'toast',
                type: $branch->estado ? 'success' : 'warning',
                message: $branch->estado
                    ? 'Sucursal activada correctamente.'
                    : 'Sucursal desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado de la sucursal.');
        }
    }
}
