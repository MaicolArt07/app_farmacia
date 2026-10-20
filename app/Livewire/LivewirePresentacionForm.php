<?php

namespace App\Livewire;

use App\Models\Presentacion;
use Livewire\Component;
use App\Livewire\Form\PresentacionForm;

class LivewirePresentacionForm extends Component
{
    public PresentacionForm $form;

    public function mount($presentacion = null)
    {
        $this->form->resetForm();

        if ($presentacion) {
            $presentacion = Presentacion::findOrFail($presentacion);
            $this->form->fillForm($presentacion);
        }
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

            return redirect()->route('presentaciones');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar la presentación.');
        }
    }

    public function cancel()
    {
        return redirect()->route('presentaciones');
    }

    public function render()
    {
        return view('livewire.presentacion.form');
    }
}
