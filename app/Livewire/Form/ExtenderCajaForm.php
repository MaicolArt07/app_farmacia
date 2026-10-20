<?php

namespace App\Livewire\Form;

use Livewire\Form;

class ExtenderCajaForm extends Form
{
    public $extendUntil = '';

    public function rules()
    {
        return [
            'extendForm.extendUntil' => 'required|date|after:now',
        ];
    }

    public function messages()
    {
        return [
            'extendForm.extendUntil.required' => 'Debe indicar la nueva fecha final.',
            'extendForm.extendUntil.after' => 'La fecha final debe ser posterior al momento actual.',
        ];
    }

    public function resetForm()
    {
        $this->extendUntil = '';
    }
}
