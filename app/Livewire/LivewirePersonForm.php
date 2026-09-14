<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Person;
use App\Livewire\Form\PersonForm;
use App\Models\Country;

class LivewirePersonForm extends Component
{
    public PersonForm $form;

    public function mount($person = null)
    {
        $this->form->resetForm();

        if ($person) {
            $person = Person::findOrFail($person);
            $this->form->fillForm($person);
        }
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            if ($this->form->id) {
                $person = Person::findOrFail($this->form->id);
                $person->update($this->form->toArray());
                $message = 'Persona actualizada correctamente.';
            } else {
                Person::create($this->form->toArray());
                $message = 'Persona registrada correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);

            return redirect()->route('person');
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
        return redirect()->route('person');
    }

    public function render()
    {
        return view('livewire.person.form', [
            'countries' => Country::where('state', 1)->get(),
        ]);
    }
}
