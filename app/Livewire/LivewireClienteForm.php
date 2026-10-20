<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Persona;
use Livewire\Component;
use App\Livewire\Form\ClienteForm;

class LivewireClienteForm extends Component
{
    public ClienteForm $form;

    public $personSearch = '';
    public $personResults = [];
    public $selectedPersonLabel = '';

    public function mount($client = null)
    {
        $this->form->resetForm();

        if ($client) {
            $client = Cliente::with('person')->findOrFail($client);
            $this->form->fillForm($client);

            if ($client->person) {
                $this->selectedPersonLabel =
                    trim($client->person->nombre . ' ' . $client->person->apellido) . ' - CI: ' . $client->person->ci;
            }
        }
    }

    public function updatedPersonSearch()
    {
        if (strlen(trim($this->personSearch)) < 2) {
            $this->personResults = [];
            return;
        }

        $this->personResults = Persona::query()
            ->where('estado', 1)
            ->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->personSearch}%")
                  ->orWhere('apellido', 'like', "%{$this->personSearch}%")
                  ->orWhere('ci', 'like', "%{$this->personSearch}%");
            })
            ->whereDoesntHave('client', function ($q) {
                if ($this->form->id) {
                    $q->where('id', '!=', $this->form->id);
                }
            })
            ->limit(8)
            ->get()
            ->map(function ($person) {
                return [
                    'id' => $person->id,
                    'label' => trim($person->nombre . ' ' . $person->apellido) . ' - CI: ' . $person->ci,
                ];
            })
            ->toArray();
    }

    public function selectPerson($id)
    {
        $person = Persona::findOrFail($id);

        $this->form->id_person = $person->id;
        $this->selectedPersonLabel = trim($person->nombre . ' ' . $person->apellido) . ' - CI: ' . $person->ci;
        $this->personSearch = '';
        $this->personResults = [];
    }

    public function clearPerson()
    {
        $this->form->id_person = null;
        $this->selectedPersonLabel = '';
        $this->personSearch = '';
        $this->personResults = [];
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            if ($this->form->id) {
                $client = Cliente::findOrFail($this->form->id);
                $client->update([
                    'id_person' => $this->form->id_person,
                    'code' => $this->form->code,
                    'tipo' => $this->form->tipo,
                    'nit' => $this->form->nit,
                    'email' => $this->form->email,
                    'estado' => $this->form->estado,
                ]);

                $message = 'Cliente actualizado correctamente.';
            } else {
                Cliente::create([
                    'id_person' => $this->form->id_person,
                    'code' => $this->form->code,
                    'tipo' => $this->form->tipo,
                    'nit' => $this->form->nit,
                    'email' => $this->form->email,
                    'estado' => $this->form->estado,
                ]);

                $message = 'Cliente registrado correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);

            return redirect()->route('clientes');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el cliente.');
        }
    }

    public function cancel()
    {
        return redirect()->route('clientes');
    }

    public function render()
    {
        return view('livewire.cliente.form');
    }
}
