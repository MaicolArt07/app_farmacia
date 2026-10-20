<?php

namespace App\Livewire;

use App\Models\MetodoPago;
use Livewire\Component;
use App\Livewire\Form\MetodoPagoForm;

class LivewireMetodoPagoForm extends Component
{
    public MetodoPagoForm $form;

    public function mount($paymentMethod = null)
    {
        $this->form->resetForm();

        if ($paymentMethod) {
            $paymentMethod = MetodoPago::findOrFail($paymentMethod);
            $this->form->fillForm($paymentMethod);
        }
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            $data = [
                'nombre' => $this->form->name,
                'codigo_sin' => $this->form->codigo_sin ?: null,
                'estado' => $this->form->state,
            ];

            if ($this->form->id) {
                $paymentMethod = MetodoPago::findOrFail($this->form->id);
                $paymentMethod->update($data);
                $message = 'Método de pago actualizado correctamente.';
            } else {
                MetodoPago::create($data);
                $message = 'Método de pago registrado correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);

            return redirect()->route('payment-methods');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el método de pago.');
        }
    }

    public function cancel()
    {
        return redirect()->route('payment-methods');
    }

    public function render()
    {
        return view('livewire.metodo-pago.form');
    }
}
