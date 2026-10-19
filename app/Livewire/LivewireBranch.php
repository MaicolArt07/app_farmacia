<?php

namespace App\Livewire;

use App\Models\Branch;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireBranch extends Component
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
        $query = Branch::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('address', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.branch.index', [
            'branches' => $query->orderBy('numero_sucursal')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $branch = Branch::findOrFail($id);
            $branch->state = $branch->state ? 0 : 1;
            $branch->save();

            $this->dispatch(
                'toast',
                type: $branch->state ? 'success' : 'warning',
                message: $branch->state
                    ? 'Sucursal activada correctamente.'
                    : 'Sucursal desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado de la sucursal.');
        }
    }
}
