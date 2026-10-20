<?php

namespace App\Livewire\Form;

use Livewire\Form;

class PermissionForm extends Form
{
    public $permissionId = null;
    public $name = '';
    public $updateMode = false;

    public function rules()
    {
        return [
            'form.name' => 'required|string|max:255|unique:permissions,name' . ($this->updateMode ? ',' . $this->permissionId : ''),
        ];
    }

    public function messages()
    {
        return [
            'form.name.required' => 'El nombre del permiso es obligatorio.',
            'form.name.unique' => 'Este nombre de permiso ya existe.',
        ];
    }

    public function resetForm()
    {
        $this->permissionId = null;
        $this->name = '';
        $this->updateMode = false;
    }
}
