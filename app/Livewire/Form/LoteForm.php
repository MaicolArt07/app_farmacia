<?php

namespace App\Livewire\Form;

use Livewire\Form;
use Illuminate\Validation\Rule;

class LotForm extends Form
{
    public $id = null;
    public $id_product = null;

    public $batch_code = '';
    public $purchase_price = '';
    public $sale_price = '';
    public $quantity_in = 0;
    public $quantity_available = 0;
    public $expiration_date = '';
    public $location = '';
    public $state = 1;

    public function rules()
    {
        return [
            'form.id_product' => 'required|exists:products,id',
            'form.batch_code' => [
                'required',
                'string',
                'max:100',
            ],
            'form.purchase_price' => 'required|numeric|min:0',
            'form.sale_price' => 'required|numeric|min:0',
            'form.quantity_in' => 'required|integer|min:0',
            'form.quantity_available' => 'required|integer|min:0',
            'form.expiration_date' => 'nullable|date',
            'form.location' => 'nullable|string|max:100',
        ];
    }

    public function messages()
    {
        return [
            'form.id_product.required' => 'Debe seleccionar un producto.',
            'form.id_product.exists' => 'El producto seleccionado no es válido.',
            'form.batch_code.required' => 'El lote es obligatorio.',
            'form.expiration_date.date' => 'La fecha de vencimiento no es válida.',
            'form.purchase_price.required' => 'El precio de compra es obligatorio.',
            'form.sale_price.required' => 'El precio de venta es obligatorio.',
            'form.quantity_in.required' => 'La cantidad ingresada es obligatoria.',
            'form.quantity_available.required' => 'La cantidad disponible es obligatoria.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->id_product = null;

        $this->batch_code = '';
        $this->purchase_price = '';
        $this->sale_price = '';
        $this->quantity_in = 0;
        $this->quantity_available = 0;
        $this->expiration_date = '';
        $this->location = '';
        $this->state = 1;
    }

    public function fillForm($lot)
    {
        $this->id = $lot->id;
        $this->id_product = $lot->id_product;

        $this->batch_code = $lot->batch_code;
        $this->purchase_price = $lot->purchase_price;
        $this->sale_price = $lot->sale_price;
        $this->quantity_in = $lot->quantity_in;
        $this->quantity_available = $lot->quantity_available;
        $this->expiration_date = optional($lot->expiration_date)->format('Y-m-d');
        $this->location = $lot->location;
        $this->state = $lot->state;
    }
}