<?php

namespace App\Livewire\Form;

use Livewire\Form;
use Illuminate\Validation\Rule;

class ProductoForm extends Form
{
    public $id = null;

    public $id_category = null;
    public $id_laboratory = null;
    public $id_presentation = null;
    public $id_sin_product_code = null;

    public $code = '';
    public $barcode = '';
    public $nombre = '';
    public $nombre_generico = '';
    public $concentracion = '';
    public $descripcion = '';

    public $precio_venta = '';
    public $stock_minimo = 0;
    public $requiere_receta = false;
    public $estado = 1;

    public function rules()
    {
        return [
            'form.id_category' => 'required|exists:categorias,id',
            'form.id_laboratory' => 'nullable|exists:laboratorios,id',
            'form.id_presentation' => 'required|exists:presentaciones,id',
            'form.id_sin_product_code' => 'nullable|exists:sin_product_codes,id',

            'form.code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('productos', 'code')->ignore($this->id),
            ],

            'form.barcode' => 'nullable|string|max:100',
            'form.nombre' => 'required|string|max:150',
            'form.nombre_generico' => 'nullable|string|max:150',
            'form.concentracion' => 'nullable|string|max:100',
            'form.descripcion' => 'nullable|string',

            'form.precio_venta' => 'required|numeric|min:0',
            'form.stock_minimo' => 'required|integer|min:0',
            'form.requiere_receta' => 'boolean',
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
            'form.id_sin_product_code.exists' => 'El código de producto SIN seleccionado no es válido.',

            'form.code.required' => 'El código es obligatorio.',
            'form.code.unique' => 'El código ya está registrado.',

            'form.nombre.required' => 'El nombre del producto es obligatorio.',
            'form.precio_venta.required' => 'El precio de venta es obligatorio.',
            'form.precio_venta.numeric' => 'El precio de venta debe ser numérico.',
            'form.stock_minimo.required' => 'El stock mínimo es obligatorio.',
            'form.stock_minimo.integer' => 'El stock mínimo debe ser entero.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;

        $this->id_category = null;
        $this->id_laboratory = null;
        $this->id_presentation = null;
        $this->id_sin_product_code = null;

        $this->code = '';
        $this->barcode = '';
        $this->nombre = '';
        $this->nombre_generico = '';
        $this->concentracion = '';
        $this->descripcion = '';

        $this->precio_venta = '';
        $this->stock_minimo = 0;
        $this->requiere_receta = false;
        $this->estado = 1;
    }

    public function fillForm($product)
    {
        $this->id = $product->id;

        $this->id_category = $product->id_category;
        $this->id_laboratory = $product->id_laboratory;
        $this->id_presentation = $product->id_presentation;
        $this->id_sin_product_code = $product->id_sin_product_code;

        $this->code = $product->code;
        $this->barcode = $product->barcode;
        $this->nombre = $product->nombre;
        $this->nombre_generico = $product->nombre_generico;
        $this->concentracion = $product->concentracion;
        $this->descripcion = $product->descripcion;

        $this->precio_venta = $product->precio_venta;
        $this->stock_minimo = $product->stock_minimo;
        $this->requiere_receta = (bool) $product->requiere_receta;
        $this->estado = $product->estado;
    }
}
