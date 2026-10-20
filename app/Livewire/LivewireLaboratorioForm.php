<?php

namespace App\Livewire;

use App\Models\Laboratorio;
use Livewire\Component;
use App\Livewire\Form\LaboratorioForm;

class LivewireLaboratorioForm extends Component
{
    public LaboratorioForm $form;

    public function mount($laboratorio = null)
    {
        $this->form->resetForm();

        if ($laboratorio) {
            $laboratorio = Laboratorio::findOrFail($laboratorio);
            $this->form->fillForm($laboratorio);
        }
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

            return redirect()->route('laboratorios');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el laboratorio.');
        }
    }

    public function cancel()
    {
        return redirect()->route('laboratorios');
    }

    public function render()
    {
        return view('livewire.laboratorio.form');
    }
}
