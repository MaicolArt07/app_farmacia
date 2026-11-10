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

    public $viewingId = null;

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
            // Solo al abrir el modal: lista de productos para "se abre en (unidad)".
            'unitOptions' => $this->modalVisible
                ? Producto::where('estado', 1)->when($this->form->id, fn ($q) => $q->where('id', '!=', $this->form->id))
                    ->orderBy('nombre')->get(['id', 'code', 'nombre'])
                    ->map(fn ($p) => ['value' => $p->id, 'label' => $p->nombre . ' (' . $p->code . ')'])->values()
                : collect(),
            'viewing' => $this->viewingId ? Producto::with(['category', 'laboratory', 'presentation', 'sinProductCode'])->find($this->viewingId) : null,
        ]);
    }

    public function view($id)
    {
        $this->viewingId = $id;
    }

    public function closeView()
    {
        $this->viewingId = null;
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

    /** Abre 1 paquete cerrado en unidades sueltas (ver App\Services\AperturaPaquete). */
    public function openPackage($id)
    {
        try {
            $pack = Producto::with('unitProduct')->findOrFail($id);
            app(\App\Services\AperturaPaquete::class)->abrir($pack, Auth::id());

            $this->dispatch('toast', type: 'success',
                message: "Paquete abierto: {$pack->unidades_por_paquete} unidades sueltas de {$pack->unitProduct?->nombre}.");
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo abrir el paquete. ' . $e->getMessage());
        }
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
                'id_categoria' => $this->form->id_categoria,
                'id_laboratorio' => $this->form->id_laboratorio ?: null,
                'id_presentacion' => $this->form->id_presentacion,
                'id_codigo_producto_sin' => $this->form->id_codigo_producto_sin ?: null,
                'id_producto_unidad' => $this->form->id_producto_unidad ?: null,
                'unidades_por_paquete' => $this->form->id_producto_unidad ? (int) $this->form->unidades_por_paquete : null,
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
                $oldPrice = (float) $product->precio_venta;
                $product->update($data);
                $message = 'Producto actualizado correctamente.';

                // Si cambió el precio, se aplica a los lotes con stock que tenían
                // el precio anterior. Los lotes que entraron con un precio propio
                // (otro ingreso) conservan el suyo.
                if ($oldPrice !== (float) $this->form->precio_venta) {
                    Lote::where('id_producto', $product->id)
                        ->where('cantidad_disponible', '>', 0)
                        ->where(fn ($q) => $q->whereNull('precio_venta')->orWhere('precio_venta', $oldPrice))
                        ->update(['precio_venta' => $this->form->precio_venta]);
                }
            } else {
                $product = Producto::create($data);
                $message = 'Producto registrado correctamente.';

                if ($this->registerInitialStock) {
                    $lot = Lote::create([
                        'id_producto' => $product->id,
                        'codigo_lote' => $this->stockForm->codigo_lote,
                        'precio_compra' => $this->stockForm->precio_compra,
                        'precio_venta' => $this->form->precio_venta,
                        'cantidad_ingresada' => $this->stockForm->cantidad,
                        'cantidad_disponible' => $this->stockForm->cantidad,
                        'fecha_vencimiento' => $this->stockForm->fecha_vencimiento ?: null,
                        'estado' => 1,
                    ]);

                    MovimientoInventario::create([
                        'id_producto' => $product->id,
                        'id_lote' => $lot->id,
                        'id_usuario' => Auth::id(),
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
