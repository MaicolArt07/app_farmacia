<?php

namespace App\Livewire\Form;

use Livewire\Form;

class CierreCajaForm extends Form
{
    /** Paso 1: monto que llegó a la cuenta por QR (según el banco/app). */
    public $qr_amount = '';

    /** Paso 2: efectivo contado con los cortes. */
    public $closing_amount = '';

    public function qrRules()
    {
        return [
            'closeForm.qr_amount' => 'required|numeric|min:0',
        ];
    }

    public function rules()
    {
        return [
            'closeForm.closing_amount' => 'required|numeric|min:0.1',
        ];
    }

    public function messages()
    {
        return [
            'closeForm.qr_amount.required' => 'Ingrese el monto que llegó a la cuenta por QR (0 si no llegó nada).',
            'closeForm.qr_amount.numeric' => 'El monto QR debe ser numérico.',
            'closeForm.qr_amount.min' => 'El monto QR no puede ser negativo.',
            'closeForm.closing_amount.required' => 'Debe registrar los cortes (monedas y billetes) contados.',
            'closeForm.closing_amount.min' => 'Debe registrar los cortes (monedas y billetes) contados.',
            'closeForm.closing_amount.numeric' => 'El monto contado debe ser numérico.',
        ];
    }

    public function resetForm()
    {
        $this->qr_amount = '';
        $this->closing_amount = '';
    }
}
