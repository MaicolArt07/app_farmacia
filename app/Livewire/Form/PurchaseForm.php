<?php

namespace App\Livewire\Form;

use Livewire\Form;

class PurchaseForm extends Form
{
    public $id = null;
    public $id_supplier = null;
    public $invoice_number = '';
    public $purchase_date = '';
    public $subtotal = 0;
    public $total = 0;
    public $observation = '';
    public $state = 1;

    public function rules()
    {
        return [
            'form.id_supplier' => 'required|exists:suppliers,id',
            'form.invoice_number' => 'nullable|string|max:50',
            'form.purchase_date' => 'required|date',
            'form.observation' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'form.id_supplier.required' => 'Debe seleccionar un proveedor.',
            'form.id_supplier.exists' => 'El proveedor seleccionado no es válido.',
            'form.purchase_date.required' => 'La fecha de compra es obligatoria.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->id_supplier = null;
        $this->invoice_number = '';
        $this->purchase_date = now()->format('Y-m-d');
        $this->subtotal = 0;
        $this->total = 0;
        $this->observation = '';
        $this->state = 1;
    }

    public function fillForm($purchase)
    {
        $this->id = $purchase->id;
        $this->id_supplier = $purchase->id_supplier;
        $this->invoice_number = $purchase->invoice_number;
        $this->purchase_date = optional($purchase->purchase_date)->format('Y-m-d');
        $this->subtotal = $purchase->subtotal;
        $this->total = $purchase->total;
        $this->observation = $purchase->observation;
        $this->state = $purchase->state;
    }
}