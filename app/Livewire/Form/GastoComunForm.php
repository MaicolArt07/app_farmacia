<?php

namespace App\Livewire\Form;

use Livewire\Form;

class GastoComunForm extends Form
{
    public $id = null;
    public $nombre = '';
    public $descripcion = '';
    public $monto_referencia = '';
    public $estado = 1;

    public function rules()
    {
        return [
            'form.nombre' => 'required|string|max:150',
            'form.descripcion' => 'nullable|string|max:255',
            'form.monto_referencia' => 'nullable|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'form.nombre.required' => 'El nombre del gasto es obligatorio.',
            'form.monto_referencia.numeric' => 'El monto de referencia debe ser numérico.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->monto_referencia = '';
        $this->estado = 1;
    }

    public function fillForm($gasto)
    {
        $this->id = $gasto->id;
        $this->nombre = $gasto->nombre;
        $this->descripcion = $gasto->descripcion;
        $this->monto_referencia = $gasto->monto_referencia;
        $this->estado = $gasto->estado;
    }
}
