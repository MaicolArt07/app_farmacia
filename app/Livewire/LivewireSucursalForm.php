<?php

namespace App\Livewire;

use App\Models\Sucursal;
use App\Models\Empresa;
use Livewire\Component;
use App\Livewire\Form\SucursalForm;

class LivewireSucursalForm extends Component
{
    public SucursalForm $form;

    public function mount($branch = null)
    {
        $this->form->resetForm();

        if ($branch) {
            $branch = Sucursal::findOrFail($branch);
            $this->form->fillForm($branch);
        }
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            $data = [
                'numero_sucursal' => $this->form->numero_sucursal,
                'punto_venta' => $this->form->punto_venta,
                'nombre' => $this->form->name,
                'municipio' => $this->form->municipio,
                'direccion' => $this->form->address ?: null,
                'telefono' => $this->form->phone ?: null,
                'estado' => $this->form->state,
            ];

            if ($this->form->id) {
                $branch = Sucursal::findOrFail($this->form->id);
                $branch->update($data);
                $message = 'Sucursal actualizada correctamente.';
            } else {
                $company = Empresa::first();

                if (!$company) {
                    $this->dispatch('toast', type: 'warning', message: 'Primero registre los datos de la Empresa antes de crear una sucursal.');
                    return redirect()->route('company.edit');
                }

                $data['id_company'] = $company->id;
                Sucursal::create($data);
                $message = 'Sucursal registrada correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);

            return redirect()->route('branches');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar la sucursal.');
        }
    }

    public function cancel()
    {
        return redirect()->route('branches');
    }

    public function render()
    {
        return view('livewire.branch.form');
    }
}
