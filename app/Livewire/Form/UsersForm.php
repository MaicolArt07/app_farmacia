<?php

namespace App\Livewire\Form;

use Livewire\Form;

class UsersForm extends Form
{
    public $id = null;
    public $id_person = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $selectedPermissions = [];
    public $updateMode = false;

    public function rules()
    {
        $rules = [
            'form.id_person' => 'nullable|exists:person,id',
            'form.name' => 'required|string|max:255',
            'form.email' => $this->updateMode
                ? 'required|email|max:255|unique:users,email,' . $this->id
                : 'required|email|max:255|unique:users,email',
            'form.selectedPermissions' => 'required|array|min:1',
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
            'form.id_person.exists' => 'La persona seleccionada no es válida.',
            'form.name.required' => 'El nombre es obligatorio.',
            'form.email.required' => 'El correo electrónico es obligatorio.',
            'form.email.email' => 'El correo debe ser válido.',
            'form.email.unique' => 'Este correo ya está registrado.',
            'form.selectedPermissions.required' => 'Debe seleccionar al menos un permiso.',
            'form.selectedPermissions.min' => 'Seleccione al menos un permiso.',
            'form.password.required' => 'La contraseña es obligatoria.',
            'form.password.min' => 'Mínimo 6 caracteres para la contraseña.',
            'form.password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    public function resetForm()
    {
        $this->id = null;
        $this->id_person = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedPermissions = [];
        $this->updateMode = false;
    }

    public function fillForm($user)
    {
        $this->id = $user->id;
        $this->id_person = $user->id_person;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedPermissions = $user->permissions->pluck('name')->toArray();
        $this->updateMode = true;
    }
}
