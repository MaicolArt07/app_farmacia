<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use App\Livewire\Form\PermissionForm;

class LivewirePermissions extends Component
{
    use WithPagination;

    public $search = '';

    public PermissionForm $form;

    public $modalVisible = false;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $query = Permission::query();

        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return view('livewire.permisos.index', [
            'permissions' => $query->orderBy('name')->paginate(5),
        ]);
    }

    public function create()
    {
        $this->form->resetForm();
        $this->modalVisible = true;
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        $this->form->permissionId = $permission->id;
        $this->form->name = $permission->name;
        $this->form->updateMode = true;
        $this->modalVisible = true;
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

        $this->modalVisible = false;
        $this->form->resetForm();
    }

    public function cancel()
    {
        $this->modalVisible = false;
        $this->form->resetForm();
    }

    public function delete($id)
    {
        Permission::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Permiso eliminado correctamente.');
    }
}
