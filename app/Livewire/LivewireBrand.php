<?php

namespace App\Livewire;

use App\Models\Brand;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireBrand extends Component
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
        $query = Brand::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.brand.index', [
            'brands' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $brand = Brand::findOrFail($id);
            $brand->state = $brand->state ? 0 : 1;
            $brand->save();

            $this->dispatch(
                'toast',
                type: $brand->state ? 'success' : 'warning',
                message: $brand->state
                    ? 'Marca activada correctamente.'
                    : 'Marca desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado de la marca.');
        }
    }
}
