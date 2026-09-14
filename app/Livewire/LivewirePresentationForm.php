<?php

namespace App\Livewire;

use App\Models\Presentation;
use Livewire\Component;
use App\Livewire\Form\PresentationForm;

class LivewirePresentationForm extends Component
{
    public PresentationForm $form;

    public function mount($presentation = null)
    {
        $this->form->resetForm();

        if ($presentation) {
            $presentation = Presentation::findOrFail($presentation);
            $this->form->fillForm($presentation);
        }
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            if ($this->form->id) {
                $presentation = Presentation::findOrFail($this->form->id);
                $presentation->update([
                    'name' => $this->form->name,
                    'description' => $this->form->description,
                    'state' => $this->form->state,
                ]);

                $message = 'Presentación actualizada correctamente.';
            } else {
                Presentation::create([
                    'name' => $this->form->name,
                    'description' => $this->form->description,
                    'state' => $this->form->state,
                ]);

                $message = 'Presentación registrada correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);

            return redirect()->route('presentations');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar la presentación.');
        }
    }

    public function cancel()
    {
        return redirect()->route('presentations');
    }

    public function render()
    {
        return view('livewire.presentation.form');
    }
}
