<?php

namespace App\Livewire;

use App\Models\Laboratory;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireLaboratory extends Component
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
        $query = Laboratory::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.laboratory.index', [
            'laboratories' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $laboratory = Laboratory::findOrFail($id);
            $laboratory->state = $laboratory->state ? 0 : 1;
            $laboratory->save();

            $this->dispatch(
                'toast',
                type: $laboratory->state ? 'success' : 'warning',
                message: $laboratory->state
                    ? 'Laboratorio activado correctamente.'
                    : 'Laboratorio desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del laboratorio.');
        }
    }
}
