<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class LivewirePermissions extends Component
{
    use WithPagination;

    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $query = Permission::query();

        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return view('livewire.permissions.index', [
            'permissions' => $query->orderBy('name')->paginate(5),
        ]);
    }

    public function delete($id)
    {
        Permission::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Permiso eliminado correctamente.');
    }
}
