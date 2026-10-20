<?php

namespace App\Livewire\Form;

use Livewire\Form;

class MetodoPagoForm extends Form
{
    public $id = null;
    public $nombre = '';
    public $codigo_sin = '';
    public $estado = 1;

    public function rules()
    {
        return [
            'form.nombre' => 'required|string|max:100',
            'form.codigo_sin' => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            'form.nombre.required' => 'El nombre del método de pago es obligatorio.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->nombre = '';
        $this->codigo_sin = '';
        $this->estado = 1;
    }

    public function fillForm($paymentMethod)
    {
        $this->id = $paymentMethod->id;
        $this->nombre = $paymentMethod->nombre;
        $this->codigo_sin = $paymentMethod->codigo_sin;
        $this->estado = $paymentMethod->estado;
    }
}
