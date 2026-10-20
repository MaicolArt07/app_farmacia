<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Persona;
use App\Models\Pais;
use App\Livewire\Form\PersonaForm;

class LivewirePersona extends Component
{
    use WithPagination;

    public $search = '';

    public PersonaForm $form;

    public $modalVisible = false;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $query = Persona::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('apellido', 'like', "%{$this->search}%")
                  ->orWhere('ci', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.persona.index', [
            'persons' => $query->paginate(10),
            'countries' => Pais::where('estado', 1)->get(),
        ]);
    }

    public function create()
    {
        $this->form->resetForm();
        $this->modalVisible = true;
    }

    public function edit($id)
    {
        $person = Persona::findOrFail($id);
        $this->form->fillForm($person);
        $this->modalVisible = true;
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
            $this->modalVisible = false;
            $this->form->resetForm();
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
        $this->modalVisible = false;
        $this->form->resetForm();
    }

    public function delete($id)
    {
        try {
            $person = Persona::findOrFail($id);
            $person->estado = $person->estado ? 0 : 1;
            $person->save();

            $message = $person->estado
                ? 'La persona fue activada correctamente.'
                : 'La persona fue desactivada correctamente.';

            $this->dispatch(
                'toast',
                type: $person->estado ? 'success' : 'warning',
                message: $message
            );
        } catch (\Throwable $e) {
            $this->dispatch(
                'toast',
                type: 'error',
                message: 'No se pudo actualizar el estado de la persona.'
            );
        }
    }
}
