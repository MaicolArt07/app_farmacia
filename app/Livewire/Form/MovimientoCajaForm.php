<?php

namespace App\Livewire\Form;

use Livewire\Form;

class MovimientoCajaForm extends Form
{
    public $type = 'INCOME';
    public $concepto = '';
    public $monto = '';
    public $observacion = '';

    public function rules()
    {
        return [
            'movementForm.type' => 'required|in:INCOME,EXPENSE',
            'movementForm.concepto' => 'required|string|max:150',
            'movementForm.monto' => 'required|numeric|min:0.01',
            'movementForm.observacion' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'movementForm.type.required' => 'Debe seleccionar el tipo de movimiento.',
            'movementForm.concepto.required' => 'El concepto es obligatorio.',
            'movementForm.monto.required' => 'El monto es obligatorio.',
            'movementForm.monto.numeric' => 'El monto debe ser numérico.',
            'movementForm.monto.min' => 'El monto debe ser mayor a cero.',
        ];
    }

    public function resetForm()
    {
        $this->type = 'INCOME';
        $this->concepto = '';
        $this->monto = '';
        $this->observacion = '';
    }
}
