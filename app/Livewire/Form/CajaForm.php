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
            'openForm.opening_amount' => 'required|numeric|min:0',
            'openForm.validity_period' => 'required|in:DAILY,WEEKLY,MONTHLY',
            'openForm.observation' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'openForm.opening_amount.required' => 'El monto inicial es obligatorio.',
            'openForm.opening_amount.numeric' => 'El monto inicial debe ser numérico.',
            'openForm.validity_period.required' => 'Debe seleccionar la duración de la caja.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->opening_date = now()->toDateString();
        $this->opening_amount = 0;
        $this->validity_period = 'DAILY';
        $this->closing_amount = '';
        $this->observation = '';
    }
}