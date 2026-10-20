<?php

namespace App\Livewire\Form;

use Livewire\Form;

class ClienteForm extends Form
{
    public $id = null;
    public $id_person = null;
    public $code = '';
    public $tipo = 'natural';
    public $nit = '';
    public $email = '';
    public $estado = 1;

    public function rules()
    {
        return [
            'form.id_person' => 'required|exists:person,id',
            'form.code' => 'required|string|max:30',
            'form.tipo' => 'required|in:natural,company',
            'form.nit' => 'nullable|string|max:30',
            'form.email' => 'nullable|email|max:100',
        ];
    }

    public function messages()
    {
        return [
            'form.id_person.required' => 'Debe seleccionar una persona.',
            'form.id_person.exists' => 'La persona seleccionada no es válida.',
            'form.code.required' => 'El código es obligatorio.',
            'form.tipo.required' => 'Debe seleccionar el tipo de cliente.',
            'form.tipo.in' => 'El tipo seleccionado no es válido.',
            'form.email.email' => 'El correo electrónico no es válido.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->id_person = null;
        $this->code = '';
        $this->tipo = 'natural';
        $this->nit = '';
        $this->email = '';
        $this->estado = 1;
    }

    public function fillForm($client)
    {
        $this->id = $client->id;
        $this->id_person = $client->id_person;
        $this->code = $client->code;
        $this->tipo = $client->tipo;
        $this->nit = $client->nit;
        $this->email = $client->email;
        $this->estado = $client->estado;
    }
}
