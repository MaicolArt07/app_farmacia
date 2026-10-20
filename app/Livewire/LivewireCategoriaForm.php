<?php

namespace App\Livewire;

use App\Models\Categoria;
use Livewire\Component;
use App\Livewire\Form\CategoriaForm;

class LivewireCategoriaForm extends Component
{
    public CategoriaForm $form;

    public function mount($categoria = null)
    {
        $this->form->resetForm();
        if ($categoria) {
            $categoria = Categoria::findOrFail($categoria);
            $this->form->fillForm($categoria);
        }
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
            return redirect()->route('categorias');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar la categoría.');
        }
    }

    public function cancel()
    {
        return redirect()->route('categorias');
    }

    public function render()
    {
        return view('livewire.categoria.form');
    }
}
