<?php

namespace App\Livewire\Form;

use Livewire\Form;

class PresentacionForm extends Form
{
    public $id = null;
    public $nombre = '';
    public $descripcion = '';
    public $codigo_sin = '';
    public $estado = 1;

    public function rules()
    {
        return [
            'form.nombre' => 'required|string|max:150',
            'form.descripcion' => 'nullable|string|max:255',
            'form.codigo_sin' => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            'form.nombre.required' => 'El nombre de la presentación es obligatorio.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->codigo_sin = '';
        $this->estado = 1;
    }

    public function fillForm($presentacion)
    {
        $this->id = $presentacion->id;
        $this->nombre = $presentacion->nombre;
        $this->descripcion = $presentacion->descripcion;
        $this->codigo_sin = $presentacion->codigo_sin;
        $this->estado = $presentacion->estado;
    }
}
