<?php

namespace App\Livewire\Form;

use Livewire\Form;

class PersonaForm extends Form
{
    public $id = null;
    public $id_country = null;
    public $nombre = '';
    public $apellido = '';
    public $ci = '';
    public $telefono = '';
    public $direccion = '';
    public $estado = 1;

    public function rules()
    {
        return [
            'form.id_country' => 'required|exists:paises,id',
            'form.nombre' => 'required|string|max:50',
            'form.apellido' => 'required|string|max:50',
            'form.ci' => 'required|string|max:20',
            'form.telefono' => 'required|string|max:20',
            'form.direccion' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'form.id_country.required' => 'Debe seleccionar un país.',
            'form.nombre.required' => 'El nombre es obligatorio.',
            'form.apellido.required' => 'El apellido es obligatorio.',
            'form.ci.required' => 'El CI es obligatorio.',
            'form.ci.unique' => 'El CI ya está registrado.',
            'form.telefono.required' => 'El teléfono es obligatorio.',
            'form.direccion.required' => 'La dirección es obligatoria.'
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->id_country = '';
        $this->nombre = '';
        $this->apellido = '';
        $this->ci = '';
        $this->telefono = '';
        $this->direccion = '';
        $this->estado = 1;
    }

    public function fillForm($person)
    {
        $this->id = $person->id;
        $this->id_country = $person->id_country;
        $this->nombre = $person->nombre;
        $this->apellido = $person->apellido;
        $this->ci = $person->ci;
        $this->telefono = $person->telefono;
        $this->direccion = $person->direccion;
        $this->estado = $person->estado;
    }
}
