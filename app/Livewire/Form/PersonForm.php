<?php

namespace App\Livewire\Form;

use Livewire\Form;

class PersonForm extends Form
{
    public $id = null;
    public $id_country = null;
    public $name = '';
    public $lastname = '';
    public $ci = '';
    public $phone = '';
    public $address = '';
    public $state = 1;

    public function rules()
    {
        return [
            'form.id_country' => 'required|exists:country,id',
            'form.name' => 'required|string|max:50',
            'form.lastname' => 'required|string|max:50',
            'form.ci' => 'required|string|max:20',
            'form.phone' => 'required|string|max:20',
            'form.address' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'form.id_country.required' => 'Debe seleccionar un país.',
            'form.name.required' => 'El nombre es obligatorio.',
            'form.lastname.required' => 'El apellido es obligatorio.',
            'form.ci.required' => 'El CI es obligatorio.',
            'form.ci.unique' => 'El CI ya está registrado.',
            'form.phone.required' => 'El teléfono es obligatorio.',
            'form.address.required' => 'La dirección es obligatoria.'
        ];
    }
    
    public function resetForm()
    {
        $this->id = null;
        $this->id_country = '';
        $this->name = '';
        $this->lastname = '';
        $this->ci = '';
        $this->phone = '';
        $this->address = '';
        $this->state = 1;
    }

    public function fillForm($person)
    {
        $this->id = $person->id;
        $this->id_country = $person->id_country;
        $this->name = $person->name;
        $this->lastname = $person->lastname;
        $this->ci = $person->ci;
        $this->phone = $person->phone;
        $this->address = $person->address;
        $this->state = $person->state;
    }
}
