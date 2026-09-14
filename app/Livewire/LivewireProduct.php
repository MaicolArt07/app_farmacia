<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireProduct extends Component
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
        $query = Product::with(['category', 'laboratory', 'presentation', 'brand']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                  ->orWhere('barcode', 'like', "%{$this->search}%")
                  ->orWhere('name', 'like', "%{$this->search}%")
                  ->orWhere('generic_name', 'like', "%{$this->search}%")
                  ->orWhere('concentration', 'like', "%{$this->search}%")
                  ->orWhereHas('category', function ($sub) {
                      $sub->where('name', 'like', "%{$this->search}%");
                  })
                  ->orWhereHas('presentation', function ($sub) {
                      $sub->where('name', 'like', "%{$this->search}%");
                  })
                  ->orWhereHas('laboratory', function ($sub) {
                      $sub->where('name', 'like', "%{$this->search}%");
                  })
                  ->orWhereHas('brand', function ($sub) {
                      $sub->where('name', 'like', "%{$this->search}%");
                  });
            });
        }

        return view('livewire.product.index', [
            'products' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->state = $product->state ? 0 : 1;
            $product->save();

            $this->dispatch(
                'toast',
                type: $product->state ? 'success' : 'warning',
                message: $product->state
                    ? 'Producto activado correctamente.'
                    : 'Producto desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del producto.');
        }
    }
}
