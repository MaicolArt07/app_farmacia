<?php

namespace App\Livewire;

use App\Models\Sucursal;
use App\Models\Empresa;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Form\SucursalForm;

class LivewireSucursal extends Component
{
    use WithPagination;

    public $search = '';

    public SucursalForm $form;

    public $modalVisible = false;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Sucursal::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('direccion', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.branch.index', [
            'branches' => $query->orderBy('numero_sucursal')->paginate(10),
        ]);
    }

    public function create()
    {
        $this->form->resetForm();
        $this->modalVisible = true;
    }

    public function edit($id)
    {
        $branch = Sucursal::findOrFail($id);
        $this->form->fillForm($branch);
        $this->modalVisible = true;
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            $data = [
                'numero_sucursal' => $this->form->numero_sucursal,
                'punto_venta' => $this->form->punto_venta,
                'nombre' => $this->form->nombre,
                'municipio' => $this->form->municipio,
                'direccion' => $this->form->direccion ?: null,
                'telefono' => $this->form->telefono ?: null,
                'estado' => $this->form->estado,
            ];

            if ($this->form->id) {
                $branch = Sucursal::findOrFail($this->form->id);
                $branch->update($data);
                $message = 'Sucursal actualizada correctamente.';
            } else {
                $company = Empresa::first();

                if (!$company) {
                    $this->dispatch('toast', type: 'warning', message: 'Primero registre los datos de la Empresa antes de crear una sucursal.');
                    $this->modalVisible = false;
                    return redirect()->route('company.edit');
                }

                $data['id_company'] = $company->id;
                Sucursal::create($data);
                $message = 'Sucursal registrada correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);
            $this->modalVisible = false;
            $this->form->resetForm();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar la sucursal.');
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
            $branch = Sucursal::findOrFail($id);
            $branch->estado = $branch->estado ? 0 : 1;
            $branch->save();

            $this->dispatch(
                'toast',
                type: $branch->estado ? 'success' : 'warning',
                message: $branch->estado
                    ? 'Sucursal activada correctamente.'
                    : 'Sucursal desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado de la sucursal.');
        }
    }
}
