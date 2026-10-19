<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Company;
use Livewire\Component;
use App\Livewire\Form\BranchForm;

class LivewireBranchForm extends Component
{
    public BranchForm $form;

    public function mount($branch = null)
    {
        $this->form->resetForm();

        if ($branch) {
            $branch = Branch::findOrFail($branch);
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
                'name' => $this->form->name,
                'municipio' => $this->form->municipio,
                'address' => $this->form->address ?: null,
                'phone' => $this->form->phone ?: null,
                'state' => $this->form->state,
            ];

            if ($this->form->id) {
                $branch = Branch::findOrFail($this->form->id);
                $branch->update($data);
                $message = 'Sucursal actualizada correctamente.';
            } else {
                $company = Company::first();

                if (!$company) {
                    $this->dispatch('toast', type: 'warning', message: 'Primero registre los datos de la Empresa antes de crear una sucursal.');
                    return redirect()->route('company.edit');
                }

                $data['id_company'] = $company->id;
                Branch::create($data);
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
