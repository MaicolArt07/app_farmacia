<?php

namespace App\Livewire\Form;

use Livewire\Form;

class CashMovementForm extends Form
{
    public $id = null;
    public $type = 'INCOME';
    public $concept = '';
    public $amount = 0;
    public $observation = '';

    public function rules()
    {
        return [
            'movementForm.type' => 'required|in:INCOME,EXPENSE',
            'movementForm.concept' => 'required|string|max:150',
            'movementForm.amount' => 'required|numeric|min:0.01',
            'movementForm.observation' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'movementForm.type.required' => 'Debe seleccionar el tipo de movimiento.',
            'movementForm.concept.required' => 'El concepto es obligatorio.',
            'movementForm.amount.required' => 'El monto es obligatorio.',
            'movementForm.amount.numeric' => 'El monto debe ser numérico.',
            'movementForm.amount.min' => 'El monto debe ser mayor a cero.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->type = 'INCOME';
        $this->concept = '';
        $this->amount = 0;
        $this->observation = '';
    }
}