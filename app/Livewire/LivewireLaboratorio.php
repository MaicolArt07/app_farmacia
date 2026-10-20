<?php

namespace App\Livewire;

use App\Models\Laboratorio;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireLaboratorio extends Component
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
        $query = Laboratorio::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('descripcion', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.laboratorio.index', [
            'laboratorios' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $laboratorio = Laboratorio::findOrFail($id);
            $laboratorio->estado = $laboratorio->estado ? 0 : 1;
            $laboratorio->save();

            $this->dispatch(
                'toast',
                type: $laboratorio->estado ? 'success' : 'warning',
                message: $laboratorio->estado
                    ? 'Laboratorio activado correctamente.'
                    : 'Laboratorio desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del laboratorio.');
        }
    }
}
