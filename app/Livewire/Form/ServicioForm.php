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

    public function rules()
    {
        return [
            'form.nombre' => 'required|string|max:150',
            'form.descripcion' => 'nullable|string',
            'form.precio' => 'required|numeric|min:0',
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
    }

    public function fillForm($service)
    {
        $this->id = $service->id;
        $this->nombre = $service->nombre;
        $this->descripcion = $service->descripcion;
        $this->precio = $service->precio;
        $this->estado = $service->estado;
    }
}
