<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Person;
use Livewire\Component;
use App\Livewire\Form\ClientForm;

class LivewireClientForm extends Component
{
    public ClientForm $form;

    public $personSearch = '';
    public $personResults = [];
    public $selectedPersonLabel = '';

    public function mount($client = null)
    {
        $this->form->resetForm();

        if ($client) {
            $client = Client::with('person')->findOrFail($client);
            $this->form->fillForm($client);

            if ($client->person) {
                $this->selectedPersonLabel =
                    trim($client->person->name . ' ' . $client->person->lastname) . ' - CI: ' . $client->person->ci;
            }
        }
    }

    public function updatedPersonSearch()
    {
        if (strlen(trim($this->personSearch)) < 2) {
            $this->personResults = [];
            return;
        }

        $this->personResults = Person::query()
            ->where('state', 1)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->personSearch}%")
                  ->orWhere('lastname', 'like', "%{$this->personSearch}%")
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
                    'label' => trim($person->name . ' ' . $person->lastname) . ' - CI: ' . $person->ci,
                ];
            })
            ->toArray();
    }

    public function selectPerson($id)
    {
        $person = Person::findOrFail($id);

        $this->form->id_person = $person->id;
        $this->selectedPersonLabel = trim($person->name . ' ' . $person->lastname) . ' - CI: ' . $person->ci;
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
                $client = Client::findOrFail($this->form->id);
                $client->update([
                    'id_person' => $this->form->id_person,
                    'code' => $this->form->code,
                    'type' => $this->form->type,
                    'nit' => $this->form->nit,
                    'email' => $this->form->email,
                    'state' => $this->form->state,
                ]);

                $message = 'Cliente actualizado correctamente.';
            } else {
                Client::create([
                    'id_person' => $this->form->id_person,
                    'code' => $this->form->code,
                    'type' => $this->form->type,
                    'nit' => $this->form->nit,
                    'email' => $this->form->email,
                    'state' => $this->form->state,
                ]);

                $message = 'Cliente registrado correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);

            return redirect()->route('clients');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el cliente.');
        }
    }

    public function cancel()
    {
        return redirect()->route('clients');
    }

    public function render()
    {
        return view('livewire.client.form');
    }
}
