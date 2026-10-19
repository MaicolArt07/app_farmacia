<?php

namespace App\Livewire\Form;

use App\Models\PaymentMethod;
use Livewire\Form;

class SaleForm extends Form
{
    public $id = null;
    public $id_client = null;
    public $sale_date = '';
    public $id_payment_method = null;
    public $subtotal = 0;
    public $discount = 0;
    public $total = 0;
    public $amount_paid = '';
    public $change_amount = 0;
    public $observation = '';
    public $state = 1;

    public function rules()
    {
        return [
            'form.id_client' => 'nullable|exists:clients,id',
            'form.sale_date' => 'required|date',
            'form.id_payment_method' => 'required|exists:payment_methods,id',
            'form.discount' => 'nullable|numeric|min:0',
            'form.amount_paid' => 'nullable|numeric|min:0',
            'form.observation' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'form.sale_date.required' => 'La fecha de venta es obligatoria.',
            'form.id_payment_method.required' => 'Debe seleccionar el método de pago.',
            'form.id_payment_method.exists' => 'El método de pago seleccionado no es válido.',
            'form.discount.numeric' => 'El descuento debe ser numérico.',
            'form.amount_paid.numeric' => 'El monto recibido debe ser numérico.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->id_client = null;
        $this->sale_date = now()->format('Y-m-d');
        $this->id_payment_method = PaymentMethod::where('name', 'Efectivo')->value('id');
        $this->subtotal = 0;
        $this->discount = 0;
        $this->total = 0;
        $this->amount_paid = '';
        $this->change_amount = 0;
        $this->observation = '';
        $this->state = 1;
    }

    public function fillForm($sale)
    {
        $this->id = $sale->id;
        $this->id_client = $sale->id_client;
        $this->sale_date = optional($sale->sale_date)->format('Y-m-d');
        $this->id_payment_method = $sale->id_payment_method;
        $this->subtotal = $sale->subtotal;
        $this->discount = $sale->discount;
        $this->total = $sale->total;
        $this->amount_paid = $sale->amount_paid;
        $this->change_amount = $sale->change_amount;
        $this->observation = $sale->observation;
        $this->state = $sale->state;
    }
}