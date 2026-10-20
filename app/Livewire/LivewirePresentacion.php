<?php

namespace App\Livewire;

use App\Models\Presentacion;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Form\PresentacionForm;

class LivewirePresentacion extends Component
{
    use WithPagination;

    public $search = '';

    public PresentacionForm $form;

    public $modalVisible = false;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Presentacion::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('descripcion', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.presentacion.index', [
            'presentaciones' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function create()
    {
        $this->form->resetForm();
        $this->modalVisible = true;
    }

    public function edit($id)
    {
        $presentacion = Presentacion::findOrFail($id);
        $this->form->fillForm($presentacion);
        $this->modalVisible = true;
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            if ($this->form->id) {
                $presentacion = Presentacion::findOrFail($this->form->id);
                $presentacion->update([
                    'nombre' => $this->form->nombre,
                    'descripcion' => $this->form->descripcion,
                    'codigo_sin' => $this->form->codigo_sin ?: null,
                    'estado' => $this->form->estado,
                ]);

                $message = 'Presentación actualizada correctamente.';
            } else {
                Presentacion::create([
                    'nombre' => $this->form->nombre,
                    'descripcion' => $this->form->descripcion,
                    'codigo_sin' => $this->form->codigo_sin ?: null,
                    'estado' => $this->form->estado,
                ]);

                $message = 'Presentación registrada correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);
            $this->modalVisible = false;
            $this->form->resetForm();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar la presentación.');
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
            $presentacion = Presentacion::findOrFail($id);
            $presentacion->estado = $presentacion->estado ? 0 : 1;
            $presentacion->save();

            $this->dispatch(
                'toast',
                type: $presentacion->estado ? 'success' : 'warning',
                message: $presentacion->estado
                    ? 'Presentación activada correctamente.'
                    : 'Presentación desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado de la presentación.');
        }
    }
}
