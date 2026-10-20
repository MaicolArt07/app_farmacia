<?php

namespace App\Livewire;

use App\Models\Producto;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireProducto extends Component
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
        $query = Producto::with(['category', 'laboratory', 'presentation']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                  ->orWhere('barcode', 'like', "%{$this->search}%")
                  ->orWhere('nombre', 'like', "%{$this->search}%")
                  ->orWhere('nombre_generico', 'like', "%{$this->search}%")
                  ->orWhere('concentracion', 'like', "%{$this->search}%")
                  ->orWhereHas('category', function ($sub) {
                      $sub->where('nombre', 'like', "%{$this->search}%");
                  })
                  ->orWhereHas('presentation', function ($sub) {
                      $sub->where('nombre', 'like', "%{$this->search}%");
                  })
                  ->orWhereHas('laboratory', function ($sub) {
                      $sub->where('nombre', 'like', "%{$this->search}%");
                  });
            });
        }

        return view('livewire.producto.index', [
            'products' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $product = Producto::findOrFail($id);
            $product->estado = $product->estado ? 0 : 1;
            $product->save();

            $this->dispatch(
                'toast',
                type: $product->estado ? 'success' : 'warning',
                message: $product->estado
                    ? 'Producto activado correctamente.'
                    : 'Producto desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del producto.');
        }
    }
}
