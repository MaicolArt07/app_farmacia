<?php

namespace App\Livewire;

use App\Models\Presentation;
use Livewire\Component;
use Livewire\WithPagination;

class LivewirePresentation extends Component
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
        $query = Presentation::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.presentation.index', [
            'presentations' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $presentation = Presentation::findOrFail($id);
            $presentation->state = $presentation->state ? 0 : 1;
            $presentation->save();

            $this->dispatch(
                'toast',
                type: $presentation->state ? 'success' : 'warning',
                message: $presentation->state
                    ? 'Presentación activada correctamente.'
                    : 'Presentación desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado de la presentación.');
        }
    }
}
