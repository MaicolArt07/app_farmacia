<?php

namespace App\Livewire\Form;

use Livewire\Form;

class LaboratoryForm extends Form
{
    public $id = null;
    public $name = '';
    public $description = '';
    public $state = 1;

    public function rules()
    {
        return [
            'form.name' => 'required|string|max:150',
            'form.description' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'form.name.required' => 'El nombre del laboratorio es obligatorio.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->name = '';
        $this->description = '';
        $this->state = 1;
    }

    public function fillForm($laboratory)
    {
        $this->id = $laboratory->id;
        $this->name = $laboratory->name;
        $this->description = $laboratory->description;
        $this->state = $laboratory->state;
    }
}