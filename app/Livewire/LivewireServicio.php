<?php

namespace App\Livewire;

use App\Models\Servicio;
use App\Models\Sucursal;
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

    protected function isSuperAdmin(): bool
    {
        return (bool) Auth::user()?->can('Super Admin');
    }

    public function render()
    {
        $query = $this->scopeToUserSucursal(Servicio::with('sucursal'));

        if (!empty($this->search)) {
            $query->where('nombre', 'like', "%{$this->search}%");
        }

        return view('livewire.servicio.index', [
            'services' => $query->orderBy('nombre')->paginate(10),
            'viewing' => $this->viewingId ? $this->scopeToUserSucursal(Servicio::with('sucursal'))->find($this->viewingId) : null,
            'isSuperAdmin' => $this->isSuperAdmin(),
            'sucursales' => $this->isSuperAdmin() ? Sucursal::orderBy('nombre')->get() : collect(),
        ]);
    }

    public function create()
    {
        $this->form->resetForm();
        $this->form->id_sucursal = Auth::user()->id_sucursal;
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

            // El Super Admin puede elegir la sucursal; cualquier otro usuario
            // siempre guarda/edita en la suya propia, sin importar lo que
            // traiga el formulario (defensa en profundidad).
            $data['id_sucursal'] = $this->isSuperAdmin() && $this->form->id_sucursal
                ? $this->form->id_sucursal
                : Auth::user()->id_sucursal;

            if ($this->form->id) {
                $service = $this->scopeToUserSucursal(Servicio::query())->findOrFail($this->form->id);
                $service->update($data);
                $message = 'Servicio actualizado correctamente.';
            } else {
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
