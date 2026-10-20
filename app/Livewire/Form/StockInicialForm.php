<?php

namespace App\Livewire\Form;

use Livewire\Form;

class StockInicialForm extends Form
{
    public $codigo_lote = '';
    public $precio_compra = '';
    public $cantidad = '';
    public $fecha_vencimiento = '';

    public function rules()
    {
        return [
            'stockForm.codigo_lote' => 'required|string|max:100',
            'stockForm.precio_compra' => 'required|numeric|min:0',
            'stockForm.cantidad' => 'required|integer|min:1',
            'stockForm.fecha_vencimiento' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'stockForm.codigo_lote.required' => 'El código de lote es obligatorio.',
            'stockForm.precio_compra.required' => 'El precio de compra es obligatorio.',
            'stockForm.precio_compra.numeric' => 'El precio de compra debe ser numérico.',
            'stockForm.cantidad.required' => 'La cantidad inicial es obligatoria.',
            'stockForm.cantidad.integer' => 'La cantidad inicial debe ser un número entero.',
            'stockForm.cantidad.min' => 'La cantidad inicial debe ser mayor a cero.',
        ];
    }

    public function resetForm()
    {
        $this->codigo_lote = '';
        $this->precio_compra = '';
        $this->cantidad = '';
        $this->fecha_vencimiento = '';
    }
}
