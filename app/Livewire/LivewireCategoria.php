<?php

namespace App\Livewire;

use App\Models\Categoria;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireCategoria extends Component
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
        $query = Categoria::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('descripcion', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.categoria.index', [
            'categorias' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $categoria->estado = $categoria->estado ? 0 : 1;
            $categoria->save();
            $this->dispatch('toast', type: $categoria->estado ? 'success' : 'warning',
                message: $categoria->estado ? 'Categoría activada correctamente.' : 'Categoría desactivada correctamente.');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado de la categoría.');
        }
    }
}
