<?php

namespace App\Livewire\Form;

use Livewire\Form;

class SaleForm extends Form
{
    public $id = null;
    public $id_client = null;
    public $sale_date = '';
    public $payment_method = 'efectivo';
    public $subtotal = 0;
    public $discount = 0;
    public $total = 0;
    public $observation = '';
    public $state = 1;

    public function rules()
    {
        return [
            'form.id_client' => 'nullable|exists:clients,id',
            'form.sale_date' => 'required|date',
            'form.payment_method' => 'required|string|max:50',
            'form.discount' => 'nullable|numeric|min:0',
            'form.observation' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'form.sale_date.required' => 'La fecha de venta es obligatoria.',
            'form.payment_method.required' => 'Debe seleccionar el método de pago.',
            'form.discount.numeric' => 'El descuento debe ser numérico.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->id_client = null;
        $this->sale_date = now()->format('Y-m-d\TH:i');
        $this->payment_method = 'efectivo';
        $this->subtotal = 0;
        $this->discount = 0;
        $this->total = 0;
        $this->observation = '';
        $this->state = 1;
    }

    public function fillForm($sale)
    {
        $this->id = $sale->id;
        $this->id_client = $sale->id_client;
        $this->sale_date = optional($sale->sale_date)->format('Y-m-d\TH:i');
        $this->payment_method = $sale->payment_method;
        $this->subtotal = $sale->subtotal;
        $this->discount = $sale->discount;
        $this->total = $sale->total;
        $this->observation = $sale->observation;
        $this->state = $sale->state;
    }
}