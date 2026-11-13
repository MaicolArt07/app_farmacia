<?php

namespace App\Livewire;

use App\Models\Servicio;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Form\ServicioForm;
use App\Support\ScopedToSucursal;
use Illuminate\Support\Facades\Auth;

class LivewireServicio extends Component
{
    use WithPagination, ScopedToSucursal;

    public $search = '';

    public ServicioForm $form;

    public $modalVisible = false;

    public $viewingId = null;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = $this->scopeToUserSucursal(Servicio::query());

        if (!empty($this->search)) {
            $query->where('nombre', 'like', "%{$this->search}%");
        }

        return view('livewire.servicio.index', [
            'services' => $query->orderBy('nombre')->paginate(10),
            'viewing' => $this->viewingId ? $this->scopeToUserSucursal(Servicio::query())->find($this->viewingId) : null,
        ]);
    }

    public function create()
    {
        $this->form->resetForm();
        $this->modalVisible = true;
    }

    public function edit($id)
    {
        $service = $this->scopeToUserSucursal(Servicio::query())->findOrFail($id);
        $this->form->fillForm($service);
        $this->modalVisible = true;
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            $data = [
                'nombre' => $this->form->nombre,
                'descripcion' => $this->form->descripcion ?: null,
                'precio' => $this->form->precio,
                'estado' => $this->form->estado,
            ];

            if ($this->form->id) {
                $service = Servicio::findOrFail($this->form->id);
                $service->update($data);
                $message = 'Servicio actualizado correctamente.';
            } else {
                $data['id_sucursal'] = Auth::user()->id_sucursal;
                Servicio::create($data);
                $message = 'Servicio registrado correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);
            $this->modalVisible = false;
            $this->form->resetForm();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el servicio.');
        }
    }

    public function cancel()
    {
        $this->modalVisible = false;
        $this->form->resetForm();
    }

    public function view($id)
    {
        $this->viewingId = $id;
    }

    public function closeView()
    {
        $this->viewingId = null;
    }

    public function delete($id)
    {
        try {
            $service = $this->scopeToUserSucursal(Servicio::query())->findOrFail($id);
            $service->estado = $service->estado ? 0 : 1;
            $service->save();

            $this->dispatch(
                'toast',
                type: $service->estado ? 'success' : 'warning',
                message: $service->estado
                    ? 'Servicio activado correctamente.'
                    : 'Servicio desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del servicio.');
        }
    }
}
