<?php

namespace App\Livewire\Form;

use Livewire\Form;

class SupplierForm extends Form
{
    public $id = null;
    public $id_person = null;
    public $company_name = '';
    public $contact_position = '';
    public $nit = '';
    public $email = '';
    public $state = 1;

    public function rules()
    {
        return [
            'form.id_person' => 'required|exists:person,id',
            'form.company_name' => 'required|string|max:150',
            'form.contact_position' => 'nullable|string|max:100',
            'form.nit' => 'nullable|string|max:30',
            'form.email' => 'nullable|email|max:100',
        ];
    }

    public function messages()
    {
        return [
            'form.id_person.required' => 'Debe seleccionar una persona.',
            'form.id_person.exists' => 'La persona seleccionada no es válida.',
            'form.company_name.required' => 'La razón social es obligatoria.',
            'form.email.email' => 'El correo electrónico no es válido.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->id_person = null;
        $this->company_name = '';
        $this->contact_position = '';
        $this->nit = '';
        $this->email = '';
        $this->state = 1;
    }

    public function fillForm($supplier)
    {
        $this->id = $supplier->id;
        $this->id_person = $supplier->id_person;
        $this->company_name = $supplier->company_name;
        $this->contact_position = $supplier->contact_position;
        $this->nit = $supplier->nit;
        $this->email = $supplier->email;
        $this->state = $supplier->state;
    }
}