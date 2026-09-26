<?php

namespace App\Livewire\Form;

use Livewire\Form;

class CashRegisterForm extends Form
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
            'form.opening_amount' => 'required|numeric|min:0',
            'form.validity_period' => 'required|in:DAILY,WEEKLY,MONTHLY',
            'form.observation' => 'nullable|string',
        ];
    }

    public function rulesClose()
    {
        return [
            'form.closing_amount' => 'required|numeric|min:0',
            'form.observation' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'form.opening_date.required' => 'La fecha de apertura es obligatoria.',
            'form.opening_amount.required' => 'El monto inicial es obligatorio.',
            'form.opening_amount.numeric' => 'El monto inicial debe ser numérico.',
            'form.closing_amount.required' => 'El monto contado es obligatorio.',
            'form.closing_amount.numeric' => 'El monto contado debe ser numérico.',
            'form.validity_period.required' => 'Debe seleccionar la duración de la caja.',
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