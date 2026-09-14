<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Traits\RolesFormTrait;

class LivewireRoles extends Component
{
    use WithPagination, RolesFormTrait;

    public $modalVisible = false;
    public $permissions = [];
    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->resetForm();
        $this->permissions = Permission::all(); // Carga todos los permisos
    }

    public function render()
    {
        $query = Role::with('permissions');

        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return view('livewire.roles.index', [
            'roles' => $query->orderBy('name')->paginate(5),
        ]);
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->resetForm();

        if ($id) {
            $role = Role::with('permissions')->findOrFail($id);
            $this->roleId = $role->id;
            $this->name = $role->name;
            $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
            $this->updateMode = true;
        } else {
            $this->updateMode = false;
        }

        $this->modalVisible = true;
    }

    public function save()
    {
        $this->validate($this->rules(), $this->messages());

        if ($this->updateMode) {
            $role = Role::findOrFail($this->roleId);
            $role->update(['name' => $this->name]);
        } else {
            $role = Role::create(['name' => $this->name]);
        }

        $role->syncPermissions($this->selectedPermissions);

        session()->flash('message', $this->updateMode
            ? 'Rol actualizado correctamente.'
            : 'Rol creado correctamente.');

        $this->modalVisible = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        Role::findOrFail($id)->delete();
        session()->flash('message', 'Rol eliminado correctamente.');
    }
}
