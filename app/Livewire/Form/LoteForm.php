<?php

namespace App\Livewire\Form;

use Livewire\Form;
use Illuminate\Validation\Rule;

class LoteForm extends Form
{
    public $id = null;
    public $id_product = null;

    public $codigo_lote = '';
    public $precio_compra = '';
    public $precio_venta = '';
    public $cantidad_ingresada = 0;
    public $cantidad_disponible = 0;
    public $fecha_vencimiento = '';
    public $ubicacion = '';
    public $estado = 1;

    public function rules()
    {
        return [
            'form.id_product' => 'required|exists:products,id',
            'form.codigo_lote' => [
                'required',
                'string',
                'max:100',
            ],
            'form.precio_compra' => 'required|numeric|min:0',
            'form.precio_venta' => 'required|numeric|min:0',
            'form.cantidad_ingresada' => 'required|integer|min:0',
            'form.cantidad_disponible' => 'required|integer|min:0',
            'form.fecha_vencimiento' => 'nullable|date',
            'form.ubicacion' => 'nullable|string|max:100',
        ];
    }

    public function messages()
    {
        return [
            'form.id_product.required' => 'Debe seleccionar un producto.',
            'form.id_product.exists' => 'El producto seleccionado no es válido.',
            'form.codigo_lote.required' => 'El lote es obligatorio.',
            'form.fecha_vencimiento.date' => 'La fecha de vencimiento no es válida.',
            'form.precio_compra.required' => 'El precio de compra es obligatorio.',
            'form.precio_venta.required' => 'El precio de venta es obligatorio.',
            'form.cantidad_ingresada.required' => 'La cantidad ingresada es obligatoria.',
            'form.cantidad_disponible.required' => 'La cantidad disponible es obligatoria.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->id_product = null;

        $this->codigo_lote = '';
        $this->precio_compra = '';
        $this->precio_venta = '';
        $this->cantidad_ingresada = 0;
        $this->cantidad_disponible = 0;
        $this->fecha_vencimiento = '';
        $this->ubicacion = '';
        $this->estado = 1;
    }

    public function fillForm($lot)
    {
        $this->id = $lot->id;
        $this->id_product = $lot->id_product;

        $this->codigo_lote = $lot->codigo_lote;
        $this->precio_compra = $lot->precio_compra;
        $this->precio_venta = $lot->precio_venta;
        $this->cantidad_ingresada = $lot->cantidad_ingresada;
        $this->cantidad_disponible = $lot->cantidad_disponible;
        $this->fecha_vencimiento = optional($lot->fecha_vencimiento)->format('Y-m-d');
        $this->ubicacion = $lot->ubicacion;
        $this->estado = $lot->estado;
    }
}
