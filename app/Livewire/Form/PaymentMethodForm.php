<?php

namespace App\Livewire\Form;

use Livewire\Form;

class PaymentMethodForm extends Form
{
    public $id = null;
    public $name = '';
    public $codigo_sin = '';
    public $state = 1;

    public function rules()
    {
        return [
            'form.name' => 'required|string|max:100',
            'form.codigo_sin' => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            'form.name.required' => 'El nombre del método de pago es obligatorio.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->name = '';
        $this->codigo_sin = '';
        $this->state = 1;
    }

    public function fillForm($paymentMethod)
    {
        $this->id = $paymentMethod->id;
        $this->name = $paymentMethod->name;
        $this->codigo_sin = $paymentMethod->codigo_sin;
        $this->state = $paymentMethod->state;
    }
}
