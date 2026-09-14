<?php

namespace App\Livewire\Form;

use Livewire\Form;

class CashRegisterForm extends Form
{
    public $id = null;
    public $opening_date = '';
    public $opening_amount = 0;
    public $closing_amount = '';
    public $observation = '';

    public function rulesOpen()
    {
        return [
            'form.opening_date' => 'required|date',
            'form.opening_amount' => 'required|numeric|min:0',
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
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->opening_date = now()->toDateString();
        $this->opening_amount = 0;
        $this->closing_amount = '';
        $this->observation = '';
    }
}