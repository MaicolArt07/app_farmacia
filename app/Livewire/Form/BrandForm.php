<?php

namespace App\Livewire\Form;

use Livewire\Form;

class BrandForm extends Form
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
            'form.name.required' => 'El nombre de la marca es obligatorio.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->name = '';
        $this->description = '';
        $this->state = 1;
    }

    public function fillForm($brand)
    {
        $this->id = $brand->id;
        $this->name = $brand->name;
        $this->description = $brand->description;
        $this->state = $brand->state;
    }
}