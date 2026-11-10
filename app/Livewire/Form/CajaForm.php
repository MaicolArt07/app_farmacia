<?php

namespace App\Livewire\Form;

use Livewire\Form;

class CajaForm extends Form
{
    public $id = null;
    public $opening_date = '';
    public $opening_amount = 0;
    public $validity_period = 'DAILY';
    public $closing_amount = '';
    public $observation = '';

    public function rulesOpen()
    {
        return [
            'openForm.opening_amount' => 'required|numeric|min:0.1',
            'openForm.observation' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'openForm.opening_amount.required' => 'Debe registrar los cortes (monedas y billetes) de la caja.',
            'openForm.opening_amount.numeric' => 'El monto inicial debe ser numérico.',
            'openForm.opening_amount.min' => 'Debe registrar los cortes (monedas y billetes) de la caja.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->opening_date = now()->toDateString();
        $this->opening_amount = '';
        $this->validity_period = 'DAILY';
        $this->closing_amount = '';
        $this->observation = '';
    }
}