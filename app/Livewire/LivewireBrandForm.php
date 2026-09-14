<?php

namespace App\Livewire;

use App\Models\Brand;
use Livewire\Component;
use App\Livewire\Form\BrandForm;

class LivewireBrandForm extends Component
{
    public BrandForm $form;

    public function mount($brand = null)
    {
        $this->form->resetForm();

        if ($brand) {
            $brand = Brand::findOrFail($brand);
            $this->form->fillForm($brand);
        }
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            if ($this->form->id) {
                $brand = Brand::findOrFail($this->form->id);
                $brand->update([
                    'name' => $this->form->name,
                    'description' => $this->form->description,
                    'state' => $this->form->state,
                ]);

                $message = 'Marca actualizada correctamente.';
            } else {
                Brand::create([
                    'name' => $this->form->name,
                    'description' => $this->form->description,
                    'state' => $this->form->state,
                ]);

                $message = 'Marca registrada correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);

            return redirect()->route('brands');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar la marca.');
        }
    }

    public function cancel()
    {
        return redirect()->route('brands');
    }

    public function render()
    {
        return view('livewire.brand.form');
    }
}
