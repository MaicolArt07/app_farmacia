<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use App\Traits\PermissionsFormTrait;

class LivewirePermissionForm extends Component
{
    use PermissionsFormTrait;

    public function mount($permission = null)
    {
        $this->resetForm();

        if ($permission) {
            $permission = Permission::findOrFail($permission);
            $this->permissionId = $permission->id;
            $this->name = $permission->name;
            $this->updateMode = true;
        }
    }

    public function save()
    {
        $this->validate($this->rules(), $this->messages());

        if ($this->updateMode) {
            $permission = Permission::findOrFail($this->permissionId);
            $permission->update(['name' => $this->name]);
            $this->dispatch('toast', type: 'success', message: 'Permiso actualizado correctamente.');
        } else {
            Permission::create(['name' => $this->name]);
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
