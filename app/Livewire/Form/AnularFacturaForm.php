<?php

namespace App\Livewire\Form;

use Livewire\Form;

class AnularFacturaForm extends Form
{
    public $anular_reason = '';

    public function rules()
    {
        return [
            'anularForm.anular_reason' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'anularForm.anular_reason.required' => 'Debe indicar el motivo de anulación.',
        ];
    }

    public function resetForm()
    {
        $this->anular_reason = '';
    }
}
