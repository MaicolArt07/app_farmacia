<?php

namespace App\Livewire\Form;

use Livewire\Form;

class ProveedorForm extends Form
{
    public $id = null;
    public $id_person = null;
    public $razon_social = '';
    public $cargo_contacto = '';
    public $nit = '';
    public $email = '';
    public $estado = 1;

    public function rules()
    {
        return [
            'form.id_person' => 'required|exists:person,id',
            'form.razon_social' => 'required|string|max:150',
            'form.cargo_contacto' => 'nullable|string|max:100',
            'form.nit' => 'nullable|string|max:30',
            'form.email' => 'nullable|email|max:100',
        ];
    }

    public function messages()
    {
        return [
            'form.id_person.required' => 'Debe seleccionar una persona.',
            'form.id_person.exists' => 'La persona seleccionada no es válida.',
            'form.razon_social.required' => 'La razón social es obligatoria.',
            'form.email.email' => 'El correo electrónico no es válido.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->id_person = null;
        $this->razon_social = '';
        $this->cargo_contacto = '';
        $this->nit = '';
        $this->email = '';
        $this->estado = 1;
    }

    public function fillForm($supplier)
    {
        $this->id = $supplier->id;
        $this->id_person = $supplier->id_person;
        $this->razon_social = $supplier->razon_social;
        $this->cargo_contacto = $supplier->cargo_contacto;
        $this->nit = $supplier->nit;
        $this->email = $supplier->email;
        $this->estado = $supplier->estado;
    }
}
