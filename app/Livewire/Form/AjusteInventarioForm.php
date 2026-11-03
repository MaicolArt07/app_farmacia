<?php

namespace App\Livewire\Form;

use Livewire\Form;

class AjusteInventarioForm extends Form
{
    public $id_producto = null;
    public $id_lote = null;
    public $type = 'INCREASE';
    public $quantity = '';
    public $reason = '';

    public function rules()
    {
        return [
            'form.id_producto' => 'required|exists:productos,id',
            'form.id_lote' => 'required|exists:lotes,id',
            'form.type' => 'required|in:INCREASE,DECREASE',
            'form.quantity' => 'required|integer|min:1',
            'form.reason' => 'required|string|min:5',
        ];
    }

    public function messages()
    {
        return [
            'form.id_producto.required' => 'Debe seleccionar un producto.',
            'form.id_lote.required' => 'Debe seleccionar un lote.',
            'form.quantity.required' => 'Debe ingresar la cantidad.',
            'form.reason.required' => 'Debe ingresar el motivo.',
        ];
    }

    public function resetForm()
    {
        $this->id_producto = null;
        $this->id_lote = null;
        $this->type = 'INCREASE';
        $this->quantity = '';
        $this->reason = '';
    }
}
