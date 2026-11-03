<?php

namespace App\Livewire\Form;

use Livewire\Form;

class CompraForm extends Form
{
    public $id = null;
    public $id_proveedor = null;
    public $numero_factura = '';
    public $fecha_compra = '';
    public $subtotal = 0;
    public $total = 0;
    public $observacion = '';
    public $estado = 1;

    public function rules()
    {
        return [
            'form.id_proveedor' => 'required|exists:proveedores,id',
            'form.numero_factura' => 'nullable|string|max:50',
            'form.fecha_compra' => 'required|date',
            'form.observacion' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'form.id_proveedor.required' => 'Debe seleccionar un proveedor.',
            'form.id_proveedor.exists' => 'El proveedor seleccionado no es válido.',
            'form.fecha_compra.required' => 'La fecha de compra es obligatoria.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->id_proveedor = null;
        $this->numero_factura = '';
        $this->fecha_compra = now()->format('Y-m-d');
        $this->subtotal = 0;
        $this->total = 0;
        $this->observacion = '';
        $this->estado = 1;
    }

    public function fillForm($purchase)
    {
        $this->id = $purchase->id;
        $this->id_proveedor = $purchase->id_proveedor;
        $this->numero_factura = $purchase->numero_factura;
        $this->fecha_compra = optional($purchase->fecha_compra)->format('Y-m-d');
        $this->subtotal = $purchase->subtotal;
        $this->total = $purchase->total;
        $this->observacion = $purchase->observacion;
        $this->estado = $purchase->estado;
    }
}
