<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;
use App\Livewire\Form\CategoryForm;

class LivewireCategoryForm extends Component
{
    public CategoryForm $form;

    public function mount($category = null)
    {
        $this->form->resetForm();

        if ($category) {
            $category = Category::findOrFail($category);
            $this->form->fillForm($category);
        }
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            if ($this->form->id) {
                $category = Category::findOrFail($this->form->id);
                $category->update([
                    'name' => $this->form->name,
                    'description' => $this->form->description,
                    'state' => $this->form->state,
                ]);

                $message = 'Categoría actualizada correctamente.';
            } else {
                Category::create([
                    'name' => $this->form->name,
                    'description' => $this->form->description,
                    'state' => $this->form->state,
                ]);

                $message = 'Categoría registrada correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);

            return redirect()->route('categories');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar la categoría.');
        }
    }

    public function cancel()
    {
        return redirect()->route('categories');
    }

    public function render()
    {
        return view('livewire.category.form');
    }
}
