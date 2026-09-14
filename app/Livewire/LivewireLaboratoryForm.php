<?php

namespace App\Livewire;

use App\Models\Laboratory;
use Livewire\Component;
use App\Livewire\Form\LaboratoryForm;

class LivewireLaboratoryForm extends Component
{
    public LaboratoryForm $form;

    public function mount($laboratory = null)
    {
        $this->form->resetForm();

        if ($laboratory) {
            $laboratory = Laboratory::findOrFail($laboratory);
            $this->form->fillForm($laboratory);
        }
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            if ($this->form->id) {
                $laboratory = Laboratory::findOrFail($this->form->id);
                $laboratory->update([
                    'name' => $this->form->name,
                    'description' => $this->form->description,
                    'state' => $this->form->state,
                ]);

                $message = 'Laboratorio actualizado correctamente.';
            } else {
                Laboratory::create([
                    'name' => $this->form->name,
                    'description' => $this->form->description,
                    'state' => $this->form->state,
                ]);

                $message = 'Laboratorio registrado correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);

            return redirect()->route('laboratories');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el laboratorio.');
        }
    }

    public function cancel()
    {
        return redirect()->route('laboratories');
    }

    public function render()
    {
        return view('livewire.laboratory.form');
    }
}
