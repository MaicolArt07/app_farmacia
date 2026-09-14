<?php

namespace App\Traits;

trait RolesFormTrait
{
    public $roleId = null;
    public $name = '';
    public $selectedPermissions = [];
    public $updateMode = false;

    public function resetForm()
    {
        $this->roleId = null;
        $this->name = '';
        $this->selectedPermissions = [];
        $this->updateMode = false;
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255|unique:roles,name' . ($this->updateMode ? ',' . $this->roleId : ''),
            'selectedPermissions' => 'required|array|min:1',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Este nombre de rol ya está en uso.',
            'selectedPermissions.required' => 'Debe seleccionar al menos un permiso.',
            'selectedPermissions.min' => 'Seleccione al menos un permiso.',
        ];
    }
}
