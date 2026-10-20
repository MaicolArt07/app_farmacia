<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use App\Livewire\Form\PermissionForm;

class LivewirePermissionForm extends Component
{
    public PermissionForm $form;

    public function mount($permission = null)
    {
        $this->form->resetForm();

        if ($permission) {
            $permission = Permission::findOrFail($permission);
            $this->form->permissionId = $permission->id;
            $this->form->name = $permission->name;
            $this->form->updateMode = true;
        }
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        if ($this->form->updateMode) {
            $permission = Permission::findOrFail($this->form->permissionId);
            $permission->update(['name' => $this->form->name]);
            $this->dispatch('toast', type: 'success', message: 'Permiso actualizado correctamente.');
        } else {
            Permission::create(['name' => $this->form->name]);
            $this->dispatch('toast', type: 'success', message: 'Permiso creado correctamente.');
        }

        return redirect()->route('permissions');
    }

    public function cancel()
    {
        return redirect()->route('permissions');
    }

    public function render()
    {
        return view('livewire.permissions.form');
    }
}
