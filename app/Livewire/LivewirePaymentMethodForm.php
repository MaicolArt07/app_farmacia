<?php

namespace App\Livewire;

use App\Models\PaymentMethod;
use Livewire\Component;
use App\Livewire\Form\PaymentMethodForm;

class LivewirePaymentMethodForm extends Component
{
    public PaymentMethodForm $form;

    public function mount($paymentMethod = null)
    {
        $this->form->resetForm();

        if ($paymentMethod) {
            $paymentMethod = PaymentMethod::findOrFail($paymentMethod);
            $this->form->fillForm($paymentMethod);
        }
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            $data = [
                'name' => $this->form->name,
                'codigo_sin' => $this->form->codigo_sin ?: null,
                'state' => $this->form->state,
            ];

            if ($this->form->id) {
                $paymentMethod = PaymentMethod::findOrFail($this->form->id);
                $paymentMethod->update($data);
                $message = 'Método de pago actualizado correctamente.';
            } else {
                PaymentMethod::create($data);
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
        return view('livewire.payment-method.form');
    }
}
