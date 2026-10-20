<?php

namespace App\Livewire\Form;

use Livewire\Form;

class AjusteInventarioForm extends Form
{
    public $id_product = null;
    public $id_lot = null;
    public $type = 'INCREASE';
    public $quantity = '';
    public $reason = '';

    public function rules()
    {
        return [
            'form.id_product' => 'required|exists:productos,id',
            'form.id_lot' => 'required|exists:lotes,id',
            'form.type' => 'required|in:INCREASE,DECREASE',
            'form.quantity' => 'required|integer|min:1',
            'form.reason' => 'required|string|min:5',
        ];
    }

    public function messages()
    {
        return [
            'form.id_product.required' => 'Debe seleccionar un producto.',
            'form.id_lot.required' => 'Debe seleccionar un lote.',
            'form.quantity.required' => 'Debe ingresar la cantidad.',
            'form.reason.required' => 'Debe ingresar el motivo.',
        ];
    }

    public function resetForm()
    {
        $this->id_product = null;
        $this->id_lot = null;
        $this->type = 'INCREASE';
        $this->quantity = '';
        $this->reason = '';
    }
}
