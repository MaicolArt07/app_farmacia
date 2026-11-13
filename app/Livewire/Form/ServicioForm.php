<?php

namespace App\Livewire\Form;

use Livewire\Form;

class ServicioForm extends Form
{
    public $id = null;
    public $nombre = '';
    public $descripcion = '';
    public $precio = '';
    public $estado = 1;
    public $id_sucursal = null;

    public function rules()
    {
        return [
            'form.nombre' => 'required|string|max:150',
            'form.descripcion' => 'nullable|string',
            'form.precio' => 'required|numeric|min:0',
            'form.id_sucursal' => 'nullable|exists:sucursales,id',
        ];
    }

    public function messages()
    {
        return [
            'form.nombre.required' => 'El nombre del servicio es obligatorio.',
            'form.precio.required' => 'El precio del servicio es obligatorio.',
            'form.precio.numeric' => 'El precio debe ser un valor numérico.',
            'form.precio.min' => 'El precio no puede ser negativo.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->precio = '';
        $this->estado = 1;
        $this->id_sucursal = null;
    }

    public function fillForm($service)
    {
        $this->id = $service->id;
        $this->nombre = $service->nombre;
        $this->descripcion = $service->descripcion;
        $this->precio = $service->precio;
        $this->estado = $service->estado;
        $this->id_sucursal = $service->id_sucursal;
    }
}
