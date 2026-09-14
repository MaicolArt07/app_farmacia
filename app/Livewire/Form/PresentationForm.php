<?php

namespace App\Livewire\Form;

use Livewire\Form;

class PresentationForm extends Form
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
            'form.name.required' => 'El nombre de la presentación es obligatorio.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->name = '';
        $this->description = '';
        $this->state = 1;
    }

    public function fillForm($presentation)
    {
        $this->id = $presentation->id;
        $this->name = $presentation->name;
        $this->description = $presentation->description;
        $this->state = $presentation->state;
    }
}