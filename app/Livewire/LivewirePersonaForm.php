<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Persona;
use App\Livewire\Form\PersonaForm;
use App\Models\Pais;

class LivewirePersonaForm extends Component
{
    public PersonaForm $form;

    public function mount($person = null)
    {
        $this->form->resetForm();

        if ($person) {
            $person = Persona::findOrFail($person);
            $this->form->fillForm($person);
        }
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            if ($this->form->id) {
                $person = Persona::findOrFail($this->form->id);
                $person->update($this->form->toArray());
                $message = 'Persona actualizada correctamente.';
            } else {
                Persona::create($this->form->toArray());
                $message = 'Persona registrada correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);

            return redirect()->route('personas');
        } catch (\Throwable $e) {
            $this->dispatch(
                'toast',
                type: 'error',
                message: 'No se pudo guardar la persona. Inténtalo nuevamente.'
            );
        }
    }

    public function cancel()
    {
        return redirect()->route('personas');
    }

    public function render()
    {
        return view('livewire.persona.form', [
            'countries' => Pais::where('estado', 1)->get(),
        ]);
    }
}
