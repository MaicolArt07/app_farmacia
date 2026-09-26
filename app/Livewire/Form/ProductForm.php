<?php

namespace App\Livewire\Form;

use Livewire\Form;
use Illuminate\Validation\Rule;

class ProductForm extends Form
{
    public $id = null;

    public $id_category = null;
    public $id_laboratory = null;
    public $id_presentation = null;

    public $code = '';
    public $barcode = '';
    public $name = '';
    public $generic_name = '';
    public $concentration = '';
    public $description = '';

    public $sale_price = '';
    public $minimum_stock = 0;
    public $requires_prescription = false;
    public $state = 1;

    public function rules()
    {
        return [
            'form.id_category' => 'required|exists:categories,id',
            'form.id_laboratory' => 'nullable|exists:laboratories,id',
            'form.id_presentation' => 'required|exists:presentations,id',

            'form.code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'code')->ignore($this->id),
            ],

            'form.barcode' => 'nullable|string|max:100',
            'form.name' => 'required|string|max:150',
            'form.generic_name' => 'nullable|string|max:150',
            'form.concentration' => 'nullable|string|max:100',
            'form.description' => 'nullable|string',

            'form.sale_price' => 'required|numeric|min:0',
            'form.minimum_stock' => 'required|integer|min:0',
            'form.requires_prescription' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'form.id_category.required' => 'Debe seleccionar una categoría.',
            'form.id_category.exists' => 'La categoría seleccionada no es válida.',

            'form.id_laboratory.exists' => 'El laboratorio seleccionado no es válido.',
            'form.id_presentation.required' => 'Debe seleccionar una presentación.',
            'form.id_presentation.exists' => 'La presentación seleccionada no es válida.',

            'form.code.required' => 'El código es obligatorio.',
            'form.code.unique' => 'El código ya está registrado.',

            'form.name.required' => 'El nombre del producto es obligatorio.',
            'form.sale_price.required' => 'El precio de venta es obligatorio.',
            'form.sale_price.numeric' => 'El precio de venta debe ser numérico.',
            'form.minimum_stock.required' => 'El stock mínimo es obligatorio.',
            'form.minimum_stock.integer' => 'El stock mínimo debe ser entero.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;

        $this->id_category = null;
        $this->id_laboratory = null;
        $this->id_presentation = null;

        $this->code = '';
        $this->barcode = '';
        $this->name = '';
        $this->generic_name = '';
        $this->concentration = '';
        $this->description = '';

        $this->sale_price = '';
        $this->minimum_stock = 0;
        $this->requires_prescription = false;
        $this->state = 1;
    }

    public function fillForm($product)
    {
        $this->id = $product->id;

        $this->id_category = $product->id_category;
        $this->id_laboratory = $product->id_laboratory;
        $this->id_presentation = $product->id_presentation;

        $this->code = $product->code;
        $this->barcode = $product->barcode;
        $this->name = $product->name;
        $this->generic_name = $product->generic_name;
        $this->concentration = $product->concentration;
        $this->description = $product->description;

        $this->sale_price = $product->sale_price;
        $this->minimum_stock = $product->minimum_stock;
        $this->requires_prescription = (bool) $product->requires_prescription;
        $this->state = $product->state;
    }
}