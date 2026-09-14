<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireUsers extends Component
{
    use WithPagination;

    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = User::with(['permissions', 'person']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.users.index', [
            'users' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            User::findOrFail($id)->delete();
            $this->dispatch('toast', type: 'success', message: 'Usuario eliminado correctamente.');
        } catch (\Throwable $e) {
            $this->dispatch(
                'toast',
                type: 'error',
                message: 'No se puede eliminar: el usuario tiene registros asociados (ventas, compras, caja, etc.).'
            );
        }
    }
}
