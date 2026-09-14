<?php

namespace App\Traits;

trait PermissionsFormTrait
{
    public $permissionId = null;
    public $name = '';
    public $updateMode = false;

    public function resetForm()
    {
        $this->permissionId = null;
        $this->name = '';
        $this->updateMode = false;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:permissions,name' . ($this->updateMode ? ',' . $this->permissionId : ''),
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del permiso es obligatorio.',
            'name.unique' => 'Este nombre de permiso ya existe.',
        ];
    }
}
