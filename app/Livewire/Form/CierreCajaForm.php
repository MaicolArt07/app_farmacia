<?php

namespace App\Livewire\Form;

use Livewire\Form;

class CierreCajaForm extends Form
{
    public $closing_amount = '';

    public function rules()
    {
        return [
            'closeForm.closing_amount' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'closeForm.closing_amount.required' => 'Debe ingresar el monto contado.',
            'closeForm.closing_amount.numeric' => 'El monto contado debe ser numérico.',
        ];
    }

    public function resetForm()
    {
        $this->closing_amount = '';
    }
}
