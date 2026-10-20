<?php

namespace App\Livewire;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Laboratorio;
use App\Models\Presentacion;
use App\Models\CodigoProductoSin;
use App\Livewire\Form\ProductoForm;
use App\Livewire\Form\StockInicialForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LivewireProducto extends Component
{
    use WithPagination;

    public $search = '';

    public ProductoForm $form;
    public StockInicialForm $stockForm;

    public $modalVisible = false;
    public $isCreating = true;
    public $step = 1;
    public $registerInitialStock = true;

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
            'categories' => Categoria::where('estado', 1)->orderBy('nombre')->get(),
            'laboratories' => Laboratorio::where('estado', 1)->orderBy('nombre')->get(),
            'presentations' => Presentacion::where('estado', 1)->orderBy('nombre')->get(),
            'sinProductCodes' => CodigoProductoSin::orderBy('descripcion')->get(),
        ]);
    }

    protected function resetStockFields()
    {
        $this->registerInitialStock = true;
        $this->stockForm->resetForm();
    }

    public function create()
    {
        $this->form->resetForm();
        $this->resetStockFields();
        $this->isCreating = true;
        $this->step = 1;
        $this->modalVisible = true;
    }

    public function edit($id)
    {
        $product = Producto::findOrFail($id);
        $this->form->fillForm($product);
        $this->resetStockFields();
        $this->isCreating = false;
        $this->step = 1;
        $this->modalVisible = true;
    }

    public function nextStep()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        $this->stockForm->codigo_lote = 'INICIAL-' . $this->form->code;
        $this->step = 2;
    }

    public function prevStep()
    {
        $this->step = 1;
    }

    public function skipStock()
    {
        $this->registerInitialStock = false;
        $this->save();
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        if ($this->isCreating && $this->registerInitialStock) {
            $this->validate($this->stockForm->rules(), $this->stockForm->messages());
        }

        DB::beginTransaction();

        try {
            $data = [
                'id_category' => $this->form->id_category,
                'id_laboratory' => $this->form->id_laboratory ?: null,
                'id_presentation' => $this->form->id_presentation,
                'id_sin_product_code' => $this->form->id_sin_product_code ?: null,
                'code' => $this->form->code,
                'barcode' => $this->form->barcode ?: null,
                'nombre' => $this->form->nombre,
                'nombre_generico' => $this->form->nombre_generico ?: null,
                'concentracion' => $this->form->concentracion ?: null,
                'descripcion' => $this->form->descripcion ?: null,
                'precio_venta' => $this->form->precio_venta,
                'stock_minimo' => $this->form->stock_minimo,
                'requiere_receta' => $this->form->requiere_receta ? 1 : 0,
                'estado' => $this->form->estado,
            ];

            if ($this->form->id) {
                $product = Producto::findOrFail($this->form->id);
                $product->update($data);
                $message = 'Producto actualizado correctamente.';
            } else {
                $product = Producto::create($data);
                $message = 'Producto registrado correctamente.';

                if ($this->registerInitialStock) {
                    $lot = Lote::create([
                        'id_product' => $product->id,
                        'codigo_lote' => $this->stockForm->codigo_lote,
                        'precio_compra' => $this->stockForm->precio_compra,
                        'precio_venta' => $this->form->precio_venta,
                        'cantidad_ingresada' => $this->stockForm->cantidad,
                        'cantidad_disponible' => $this->stockForm->cantidad,
                        'fecha_vencimiento' => $this->stockForm->fecha_vencimiento ?: null,
                        'estado' => 1,
                    ]);

                    MovimientoInventario::create([
                        'id_product' => $product->id,
                        'id_lot' => $lot->id,
                        'id_user' => Auth::id(),
                        'tipo_movimiento' => 'IN',
                        'reference_type' => 'INITIAL_STOCK',
                        'reference_id' => null,
                        'quantity' => $this->stockForm->cantidad,
                        'stock_before' => 0,
                        'stock_after' => $this->stockForm->cantidad,
                        'descripcion' => 'Stock inicial registrado junto con el producto.',
                    ]);

                    $message = 'Producto y stock inicial registrados correctamente.';
                }
            }

            DB::commit();

            $this->dispatch('toast', type: 'success', message: $message);
            $this->modalVisible = false;
            $this->form->resetForm();
            $this->resetStockFields();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el producto.');
        }
    }

    public function cancel()
    {
        $this->modalVisible = false;
        $this->form->resetForm();
        $this->resetStockFields();
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
