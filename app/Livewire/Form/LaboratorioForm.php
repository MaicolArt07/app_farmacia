<?php

namespace App\Livewire\Form;

use Livewire\Form;

class LaboratorioForm extends Form
{
    public $id = null;
    public $nombre = '';
    public $descripcion = '';
    public $estado = 1;

    public function rules()
    {
        return [
            'form.nombre' => 'required|string|max:150',
            'form.descripcion' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'form.nombre.required' => 'El nombre del laboratorio es obligatorio.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->estado = 1;
    }

    public function fillForm($laboratorio)
    {
        $this->id = $laboratorio->id;
        $this->nombre = $laboratorio->nombre;
        $this->descripcion = $laboratorio->descripcion;
        $this->estado = $laboratorio->estado;
    }
}
