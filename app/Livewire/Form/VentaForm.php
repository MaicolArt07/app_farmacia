<?php

namespace App\Livewire\Form;

use App\Models\MetodoPago;
use Livewire\Form;

class VentaForm extends Form
{
    public $id = null;
    public $id_cliente = null;
    public $fecha_venta = '';
    public $id_metodo_pago = null;
    public $subtotal = 0;
    public $descuento = 0;
    public $total = 0;
    public $monto_pagado = '';
    public $monto_cambio = 0;
    public $observacion = '';
    public $estado = 1;

    public function rules()
    {
        return [
            'form.id_cliente' => 'nullable|exists:clientes,id',
            'form.fecha_venta' => 'required|date',
            'form.id_metodo_pago' => 'required|exists:metodos_pago,id',
            'form.descuento' => 'nullable|numeric|min:0',
            'form.monto_pagado' => 'nullable|numeric|min:0',
            'form.observacion' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'form.fecha_venta.required' => 'La fecha de venta es obligatoria.',
            'form.id_metodo_pago.required' => 'Debe seleccionar el método de pago.',
            'form.id_metodo_pago.exists' => 'El método de pago seleccionado no es válido.',
            'form.descuento.numeric' => 'El descuento debe ser numérico.',
            'form.monto_pagado.numeric' => 'El monto recibido debe ser numérico.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->id_cliente = null;
        $this->fecha_venta = now()->format('Y-m-d');
        $this->id_metodo_pago = MetodoPago::where('nombre', 'Efectivo')->value('id');
        $this->subtotal = 0;
        $this->descuento = 0;
        $this->total = 0;
        $this->monto_pagado = '';
        $this->monto_cambio = 0;
        $this->observacion = '';
        $this->estado = 1;
    }

    public function fillForm($sale)
    {
        $this->id = $sale->id;
        $this->id_cliente = $sale->id_cliente;
        $this->fecha_venta = optional($sale->fecha_venta)->format('Y-m-d');
        $this->id_metodo_pago = $sale->id_metodo_pago;
        $this->subtotal = $sale->subtotal;
        $this->descuento = $sale->descuento;
        $this->total = $sale->total;
        $this->monto_pagado = $sale->monto_pagado;
        $this->monto_cambio = $sale->monto_cambio;
        $this->observacion = $sale->observacion;
        $this->estado = $sale->estado;
    }
}
