<?php

namespace App\Livewire;

use App\Models\Persona;
use App\Models\Proveedor;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Form\ProveedorForm;

class LivewireProveedor extends Component
{
    use WithPagination;

    public $search = '';

    public ProveedorForm $form;

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
        $query = Proveedor::with('person');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('razon_social', 'like', "%{$this->search}%")
                  ->orWhere('cargo_contacto', 'like', "%{$this->search}%")
                  ->orWhere('nit', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhereHas('person', function ($personQuery) {
                      $personQuery->where('nombre', 'like', "%{$this->search}%")
                          ->orWhere('apellido', 'like', "%{$this->search}%")
                          ->orWhere('ci', 'like', "%{$this->search}%");
                  });
            });
        }

        return view('livewire.proveedor.index', [
            'suppliers' => $query->orderBy('id', 'desc')->paginate(10),
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
        $supplier = Proveedor::with('person')->findOrFail($id);
        $this->form->fillForm($supplier);

        $this->selectedPersonLabel = '';
        if ($supplier->person) {
            $this->selectedPersonLabel =
                trim($supplier->person->nombre . ' ' . $supplier->person->apellido) . ' - CI: ' . $supplier->person->ci;
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
            ->whereDoesntHave('supplier', function ($q) {
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
                $supplier = Proveedor::findOrFail($this->form->id);
                $supplier->update([
                    'id_person' => $this->form->id_person,
                    'razon_social' => $this->form->razon_social,
                    'cargo_contacto' => $this->form->cargo_contacto,
                    'nit' => $this->form->nit,
                    'email' => $this->form->email,
                    'estado' => $this->form->estado,
                ]);

                $message = 'Proveedor actualizado correctamente.';
            } else {
                Proveedor::create([
                    'id_person' => $this->form->id_person,
                    'razon_social' => $this->form->razon_social,
                    'cargo_contacto' => $this->form->cargo_contacto,
                    'nit' => $this->form->nit,
                    'email' => $this->form->email,
                    'estado' => $this->form->estado,
                ]);

                $message = 'Proveedor registrado correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);
            $this->modalVisible = false;
            $this->form->resetForm();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el proveedor.');
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
            $supplier = Proveedor::findOrFail($id);
            $supplier->estado = $supplier->estado ? 0 : 1;
            $supplier->save();

            $this->dispatch(
                'toast',
                type: $supplier->estado ? 'success' : 'warning',
                message: $supplier->estado
                    ? 'Proveedor activado correctamente.'
                    : 'Proveedor desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del proveedor.');
        }
    }
}
