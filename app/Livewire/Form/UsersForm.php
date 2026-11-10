<?php

namespace App\Livewire\Form;

use Livewire\Form;

class UsersForm extends Form
{
    public $id = null;
    public $id_persona = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $selectedPermissions = [];
    public $selectedRoles = [];
    public $updateMode = false;

    public function rules()
    {
        $rules = [
            'form.id_persona' => 'nullable|exists:personas,id',
            'form.name' => $this->updateMode
                ? 'required|string|max:255|unique:users,name,' . $this->id
                : 'required|string|max:255|unique:users,name',
            'form.email' => $this->updateMode
                ? 'required|email|max:255|unique:users,email,' . $this->id
                : 'required|email|max:255|unique:users,email',
            'form.selectedRoles' => 'array',
            'form.selectedPermissions' => 'array',
        ];

        if (!$this->updateMode) {
            $rules['form.password'] = 'required|min:6|confirmed';
        } elseif ($this->password || $this->password_confirmation) {
            $rules['form.password'] = 'nullable|min:6|confirmed';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'form.id_persona.exists' => 'La persona seleccionada no es válida.',
            'form.name.required' => 'El nombre es obligatorio.',
            'form.name.unique' => 'Ya existe un usuario con ese nombre (se usa también para iniciar sesión).',
            'form.email.required' => 'El correo electrónico es obligatorio.',
            'form.email.email' => 'El correo debe ser válido.',
            'form.email.unique' => 'Este correo ya está registrado.',
            'form.password.required' => 'La contraseña es obligatoria.',
            'form.password.min' => 'Mínimo 6 caracteres para la contraseña.',
            'form.password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->id_persona = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedPermissions = [];
        $this->selectedRoles = [];
        $this->updateMode = false;
    }

    public function fillForm($user)
    {
        $this->id = $user->id;
        $this->id_persona = $user->id_persona;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->selectedPermissions = $user->permissions->pluck('name')->toArray();
        $this->updateMode = true;
    }
}
