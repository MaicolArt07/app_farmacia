<?php

namespace App\Livewire;

use App\Models\Categoria;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Form\CategoriaForm;

class LivewireCategoria extends Component
{
    use WithPagination;

    public $search = '';

    public CategoriaForm $form;

    public $modalVisible = false;

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

    public function create()
    {
        $this->form->resetForm();
        $this->modalVisible = true;
    }

    public function edit($id)
    {
        $categoria = Categoria::findOrFail($id);
        $this->form->fillForm($categoria);
        $this->modalVisible = true;
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());
        try {
            if ($this->form->id) {
                $categoria = Categoria::findOrFail($this->form->id);
                $categoria->update(['nombre' => $this->form->nombre, 'descripcion' => $this->form->descripcion, 'estado' => $this->form->estado]);
                $message = 'Categoría actualizada correctamente.';
            } else {
                Categoria::create(['nombre' => $this->form->nombre, 'descripcion' => $this->form->descripcion, 'estado' => $this->form->estado]);
                $message = 'Categoría registrada correctamente.';
            }
            $this->dispatch('toast', type: 'success', message: $message);
            $this->modalVisible = false;
            $this->form->resetForm();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar la categoría.');
        }
    }

    public function cancel()
    {
        $this->modalVisible = false;
        $this->form->resetForm();
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
