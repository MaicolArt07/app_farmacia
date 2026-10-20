<?php

namespace App\Livewire;

use App\Models\Persona;
use App\Models\Proveedor;
use Livewire\Component;
use App\Livewire\Form\ProveedorForm;

class LivewireProveedorForm extends Component
{
    public ProveedorForm $form;

    public $personSearch = '';
    public $personResults = [];
    public $selectedPersonLabel = '';

    public function mount($supplier = null)
    {
        $this->form->resetForm();

        if ($supplier) {
            $supplier = Proveedor::with('person')->findOrFail($supplier);
            $this->form->fillForm($supplier);

            if ($supplier->person) {
                $this->selectedPersonLabel =
                    trim($supplier->person->nombre . ' ' . $supplier->person->apellido) . ' - CI: ' . $supplier->person->ci;
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

            return redirect()->route('proveedores');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el proveedor.');
        }
    }

    public function cancel()
    {
        return redirect()->route('proveedores');
    }

    public function render()
    {
        return view('livewire.proveedor.form');
    }
}
