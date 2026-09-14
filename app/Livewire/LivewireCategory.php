<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireCategory extends Component
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
        $query = Category::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.category.index', [
            'categories' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->state = $category->state ? 0 : 1;
            $category->save();

            $this->dispatch(
                'toast',
                type: $category->state ? 'success' : 'warning',
                message: $category->state
                    ? 'Categoría activada correctamente.'
                    : 'Categoría desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado de la categoría.');
        }
    }
}
