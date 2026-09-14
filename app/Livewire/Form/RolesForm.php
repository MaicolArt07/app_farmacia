<?php

namespace App\Livewire\Form;

use Livewire\Form;

class RolesForm extends Form
{
    public $role_id = null;
    public $name = '';
    public $selectedPermissions = [];
    public $updateMode = false;

    public function resetForm()
    {
        $this->role_id = null;
        $this->name = '';
        $this->selectedPermissions = [];
        $this->updateMode = false;
    }

    public function rules()
    {
        $uniqueRule = $this->updateMode
            ? 'required|string|max:255|unique:roles,name,' . $this->role_id
            : 'required|string|max:255|unique:roles,name';

        return [
            'name' => $uniqueRule,
            'selectedPermissions' => 'required|array|min:1',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Este rol ya existe.',
            'selectedPermissions.required' => 'Debe seleccionar al menos un permiso.',
            'selectedPermissions.min' => 'Debe seleccionar al menos un permiso.',
        ];
    }
}
