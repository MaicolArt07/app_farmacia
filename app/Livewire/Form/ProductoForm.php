<?php

namespace App\Livewire\Form;

use Livewire\Form;
use Illuminate\Validation\Rule;

class ProductoForm extends Form
{
    public $id = null;

    public $id_categoria = null;
    public $id_laboratorio = null;
    public $id_presentacion = null;
    public $id_codigo_producto_sin = null;

    // Paquete que también se vende suelto: se abre en este producto "unidad".
    public $id_producto_unidad = null;
    public $unidades_por_paquete = null;

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
            'form.id_categoria' => 'required|exists:categorias,id',
            'form.id_laboratorio' => 'nullable|exists:laboratorios,id',
            'form.id_presentacion' => 'required|exists:presentaciones,id',
            'form.id_codigo_producto_sin' => 'nullable|exists:codigos_producto_sin,id',

            'form.id_producto_unidad' => ['nullable', 'exists:productos,id', Rule::notIn(array_filter([$this->id]))],
            'form.unidades_por_paquete' => 'nullable|required_with:form.id_producto_unidad|integer|min:2',

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
            'form.id_categoria.required' => 'Debe seleccionar una categoría.',
            'form.id_categoria.exists' => 'La categoría seleccionada no es válida.',

            'form.id_laboratorio.exists' => 'El laboratorio seleccionado no es válido.',
            'form.id_presentacion.required' => 'Debe seleccionar una presentación.',
            'form.id_presentacion.exists' => 'La presentación seleccionada no es válida.',
            'form.id_codigo_producto_sin.exists' => 'El código de producto SIN seleccionado no es válido.',

            'form.id_producto_unidad.not_in' => 'Un paquete no puede abrirse en sí mismo.',
            'form.unidades_por_paquete.required_with' => 'Indique cuántas unidades trae cada paquete.',
            'form.unidades_por_paquete.min' => 'Un paquete debe traer al menos 2 unidades.',

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

        $this->id_categoria = null;
        $this->id_laboratorio = null;
        $this->id_presentacion = null;
        $this->id_codigo_producto_sin = null;
        $this->id_producto_unidad = null;
        $this->unidades_por_paquete = null;

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

        $this->id_categoria = $product->id_categoria;
        $this->id_laboratorio = $product->id_laboratorio;
        $this->id_presentacion = $product->id_presentacion;
        $this->id_codigo_producto_sin = $product->id_codigo_producto_sin;
        $this->id_producto_unidad = $product->id_producto_unidad;
        $this->unidades_por_paquete = $product->unidades_por_paquete;

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
