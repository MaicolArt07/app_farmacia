<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Persona;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Form\ClienteForm;

class LivewireCliente extends Component
{
    use WithPagination;

    public $search = '';

    public ClienteForm $form;

    public $modalVisible = false;

    public $personSearch = '';
    public $personResults = [];
    public $selectedPersonLabel = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Cliente::with('person');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                  ->orWhere('nit', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhereHas('person', function ($personQuery) {
                      $personQuery->where('nombre', 'like', "%{$this->search}%")
                          ->orWhere('apellido', 'like', "%{$this->search}%")
                          ->orWhere('ci', 'like', "%{$this->search}%");
                  });
            });
        }

        return view('livewire.cliente.index', [
            'clients' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function create()
    {
        $this->form->resetForm();
        $this->selectedPersonLabel = '';
        $this->personSearch = '';
        $this->personResults = [];
        $this->modalVisible = true;
    }

    public function edit($id)
    {
        $client = Cliente::with('person')->findOrFail($id);
        $this->form->fillForm($client);

        $this->selectedPersonLabel = '';
        if ($client->person) {
            $this->selectedPersonLabel =
                trim($client->person->nombre . ' ' . $client->person->apellido) . ' - CI: ' . $client->person->ci;
        }

        $this->personSearch = '';
        $this->personResults = [];
        $this->modalVisible = true;
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
            $this->modalVisible = false;
            $this->form->resetForm();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el cliente.');
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
            $client = Cliente::findOrFail($id);
            $client->estado = $client->estado ? 0 : 1;
            $client->save();

            $this->dispatch(
                'toast',
                type: $client->estado ? 'success' : 'warning',
                message: $client->estado
                    ? 'Cliente activado correctamente.'
                    : 'Cliente desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del cliente.');
        }
    }
}
