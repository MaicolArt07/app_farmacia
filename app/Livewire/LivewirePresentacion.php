<?php

namespace App\Livewire;

use App\Models\Presentacion;
use Livewire\Component;
use Livewire\WithPagination;

class LivewirePresentacion extends Component
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
        $query = Presentacion::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('descripcion', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.presentacion.index', [
            'presentaciones' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $presentacion = Presentacion::findOrFail($id);
            $presentacion->estado = $presentacion->estado ? 0 : 1;
            $presentacion->save();

            $this->dispatch(
                'toast',
                type: $presentacion->estado ? 'success' : 'warning',
                message: $presentacion->estado
                    ? 'Presentación activada correctamente.'
                    : 'Presentación desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado de la presentación.');
        }
    }
}
