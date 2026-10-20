<?php

namespace App\Livewire;

use App\Models\Laboratorio;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Form\LaboratorioForm;

class LivewireLaboratorio extends Component
{
    use WithPagination;

    public $search = '';

    public LaboratorioForm $form;

    public $modalVisible = false;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Laboratorio::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('descripcion', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.laboratorio.index', [
            'laboratorios' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function create()
    {
        $this->form->resetForm();
        $this->modalVisible = true;
    }

    public function edit($id)
    {
        $laboratorio = Laboratorio::findOrFail($id);
        $this->form->fillForm($laboratorio);
        $this->modalVisible = true;
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            if ($this->form->id) {
                $laboratorio = Laboratorio::findOrFail($this->form->id);
                $laboratorio->update([
                    'nombre' => $this->form->nombre,
                    'descripcion' => $this->form->descripcion,
                    'estado' => $this->form->estado,
                ]);

                $message = 'Laboratorio actualizado correctamente.';
            } else {
                Laboratorio::create([
                    'nombre' => $this->form->nombre,
                    'descripcion' => $this->form->descripcion,
                    'estado' => $this->form->estado,
                ]);

                $message = 'Laboratorio registrado correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);
            $this->modalVisible = false;
            $this->form->resetForm();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el laboratorio.');
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
            $laboratorio = Laboratorio::findOrFail($id);
            $laboratorio->estado = $laboratorio->estado ? 0 : 1;
            $laboratorio->save();

            $this->dispatch(
                'toast',
                type: $laboratorio->estado ? 'success' : 'warning',
                message: $laboratorio->estado
                    ? 'Laboratorio activado correctamente.'
                    : 'Laboratorio desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del laboratorio.');
        }
    }
}
