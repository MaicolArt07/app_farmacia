<?php

namespace App\Livewire;

use App\Models\Lote;
use App\Models\Producto;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Form\LoteForm;

class LivewireLote extends Component
{
    use WithPagination;

    public $search = '';

    public LoteForm $form;

    public $modalVisible = false;

    public $viewingId = null;

    public $productSearch = '';
    public $productResults = [];
    public $selectedProductLabel = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Lote::with('product');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('codigo_lote', 'like', "%{$this->search}%")
                  ->orWhere('ubicacion', 'like', "%{$this->search}%")
                  ->orWhere('fecha_vencimiento', 'like', "%{$this->search}%")
                  ->orWhereHas('product', function ($sub) {
                      $sub->where('code', 'like', "%{$this->search}%")
                          ->orWhere('barcode', 'like', "%{$this->search}%")
                          ->orWhere('nombre', 'like', "%{$this->search}%")
                          ->orWhere('nombre_generico', 'like', "%{$this->search}%");
                  });
            });
        }

        return view('livewire.lote.index', [
            'lots' => $query->orderBy('fecha_vencimiento', 'asc')
                            ->orderBy('id', 'desc')
                            ->paginate(10),
            'viewing' => $this->viewingId ? Lote::with('product')->find($this->viewingId) : null,
            'cuadre' => $this->cuadreLotId ? $this->cuadre($this->cuadreLotId) : null,
        ]);
    }

    /* ---------- Cuadre del lote: entradas, ventas, ajustes vs. disponible ---------- */

    public $cuadreLotId = null;

    public function openCuadre($id)
    {
        $this->cuadreLotId = Lote::findOrFail($id)->id;
    }

    public function closeCuadre()
    {
        $this->cuadreLotId = null;
    }

    /** Arma el cuadre de un lote desde su Kardex. */
    protected function cuadre(int $lotId): array
    {
        $lot = Lote::with('product')->findOrFail($lotId);

        $moves = \App\Models\MovimientoInventario::with('user')
            ->where('id_lote', $lot->id)->orderBy('id')->get()
            ->map(function ($m) {
                $q = (int) $m->quantity;
                $sign = match ($m->tipo_movimiento) {
                    'IN', 'RETURN', 'CANCEL' => 1,
                    'OUT' => -1,
                    default => ((int) $m->stock_after < (int) $m->stock_before) ? -1 : 1, // ADJUSTMENT
                };
                $label = match (true) {
                    $m->reference_type === 'INVENTORY_IMPORT' => 'Carga inicial',
                    $m->reference_type === 'STOCK_ENTRY' => 'Ingreso de mercadería',
                    $m->reference_type === 'SALE' => 'Venta #' . $m->reference_id,
                    $m->reference_type === 'SALE_CANCEL' => 'Venta #' . $m->reference_id . ' cancelada (vuelve)',
                    $m->reference_type === 'SALE_RETURN' => 'Devolución de cliente',
                    $m->reference_type === 'INVENTORY_ADJUSTMENT' => $sign > 0 ? 'Ajuste (+)' : 'Ajuste (−)',
                    $m->reference_type === 'PACKAGE_OPEN' => $sign > 0 ? 'Apertura de paquete (+)' : 'Apertura de paquete (−)',
                    default => $m->reference_type,
                };
                $group = match (true) {
                    in_array($m->reference_type, ['INVENTORY_IMPORT', 'STOCK_ENTRY'], true) || ($m->reference_type === 'PACKAGE_OPEN' && $sign > 0) => 'entradas',
                    $m->reference_type === 'SALE' => 'ventas',
                    in_array($m->reference_type, ['SALE_CANCEL', 'SALE_RETURN'], true) => 'devueltas',
                    $m->reference_type === 'INVENTORY_ADJUSTMENT' => $sign > 0 ? 'ajuste_mas' : 'ajuste_menos',
                    default => 'otros',
                };

                return (object) [
                    'fecha' => $m->created_at, 'label' => $label, 'group' => $group,
                    'delta' => $sign * $q, 'user' => $m->user?->name, 'descripcion' => $m->descripcion,
                ];
            });

        $sum = fn ($g) => (int) $moves->where('group', $g)->sum(fn ($m) => abs($m->delta));
        $calculado = (int) $moves->sum('delta');
        $ventas = $moves->where('group', 'ventas');

        return [
            'lot' => $lot,
            'moves' => $moves,
            'entradas' => $sum('entradas'),
            'vendidas' => $sum('ventas'),
            'num_ventas' => $ventas->count(),
            'devueltas' => $sum('devueltas'),
            'ajuste_mas' => $sum('ajuste_mas'),
            'ajuste_menos' => $sum('ajuste_menos'),
            'otros' => (int) $moves->where('group', 'otros')->sum('delta'),
            'calculado' => $calculado,
            'disponible' => (int) $lot->cantidad_disponible,
            'diferencia' => (int) $lot->cantidad_disponible - $calculado,
        ];
    }

    public function create()
    {
        $this->form->resetForm();
        $this->selectedProductLabel = '';
        $this->productSearch = '';
        $this->productResults = [];
        $this->modalVisible = true;
    }

    public function edit($id)
    {
        $lot = Lote::with('product')->findOrFail($id);
        $this->form->fillForm($lot);

        $this->selectedProductLabel = '';
        if ($lot->product) {
            $this->selectedProductLabel = $lot->product->code . ' - ' . $lot->product->nombre .
                ($lot->product->concentracion ? ' | ' . $lot->product->concentracion : '');
        }

        $this->productSearch = '';
        $this->productResults = [];
        $this->modalVisible = true;
    }

    public function updatedProductSearch()
    {
        if (strlen(trim($this->productSearch)) < 2) {
            $this->productResults = [];
            return;
        }

        $this->productResults = Producto::query()
            ->where('estado', 1)
            ->where(function ($q) {
                $q->where('code', 'like', "%{$this->productSearch}%")
                  ->orWhere('barcode', 'like', "%{$this->productSearch}%")
                  ->orWhere('nombre', 'like', "%{$this->productSearch}%")
                  ->orWhere('nombre_generico', 'like', "%{$this->productSearch}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'label' => $product->code . ' - ' . $product->nombre .
                        ($product->concentracion ? ' | ' . $product->concentracion : ''),
                ];
            })
            ->toArray();
    }

    public function selectProduct($id)
    {
        $product = Producto::findOrFail($id);

        $this->form->id_producto = $product->id;
        $this->selectedProductLabel = $product->code . ' - ' . $product->nombre .
            ($product->concentracion ? ' | ' . $product->concentracion : '');

        if (!$this->form->precio_venta || $this->form->precio_venta == 0) {
            $this->form->precio_venta = $product->precio_venta;
        }

        $this->productSearch = '';
        $this->productResults = [];
    }

    public function clearProduct()
    {
        $this->form->id_producto = null;
        $this->selectedProductLabel = '';
        $this->productSearch = '';
        $this->productResults = [];
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        if ($this->form->cantidad_disponible > $this->form->cantidad_ingresada) {
            $this->dispatch('toast', type: 'warning', message: 'La cantidad disponible no puede ser mayor a la cantidad ingresada.');
            return;
        }

        try {
            $expirationDate = $this->form->fecha_vencimiento ?: null;

            $data = [
                'id_producto' => $this->form->id_producto,
                'codigo_lote' => $this->form->codigo_lote,
                'precio_compra' => $this->form->precio_compra,
                'precio_venta' => $this->form->precio_venta,
                'cantidad_ingresada' => $this->form->cantidad_ingresada,
                'cantidad_disponible' => $this->form->cantidad_disponible,
                'fecha_vencimiento' => $expirationDate,
                'ubicacion' => $this->form->ubicacion ?: null,
                'estado' => $this->form->estado,
            ];

            if ($this->form->id) {
                $lot = Lote::findOrFail($this->form->id);
                $before = (int) $lot->cantidad_disponible;
                $lot->update($data);
                $message = 'Lote actualizado correctamente.';

                // Si cambió el disponible a mano, queda registrado en el Kardex
                // (así el "Cuadre del lote" sigue cuadrando).
                $after = (int) $lot->cantidad_disponible;
                if ($after !== $before) {
                    \App\Models\MovimientoInventario::create([
                        'id_producto' => $lot->id_producto,
                        'id_lote' => $lot->id,
                        'id_usuario' => auth()->id(),
                        'tipo_movimiento' => 'ADJUSTMENT',
                        'reference_type' => 'INVENTORY_ADJUSTMENT',
                        'reference_id' => null,
                        'quantity' => abs($after - $before),
                        'stock_before' => $before,
                        'stock_after' => $after,
                        'descripcion' => "Edición manual del lote {$lot->codigo_lote}: disponible {$before} -> {$after}.",
                    ]);
                }
            } else {
                $exists = Lote::where('id_producto', $this->form->id_producto)
                    ->where('codigo_lote', $this->form->codigo_lote)
                    ->when($expirationDate, fn ($q) => $q->where('fecha_vencimiento', $expirationDate))
                    ->when(!$expirationDate, fn ($q) => $q->whereNull('fecha_vencimiento'))
                    ->exists();

                if ($exists) {
                    $this->dispatch('toast', type: 'warning', message: 'Ya existe un lote con ese producto, código y vencimiento.');
                    return;
                }

                $lot = Lote::create($data);
                $message = 'Lote registrado correctamente.';

                // Ingreso registrado en el Kardex.
                if ((int) $lot->cantidad_disponible > 0) {
                    $prev = (int) Lote::where('id_producto', $lot->id_producto)->where('id', '!=', $lot->id)
                        ->where('estado', 1)->sum('cantidad_disponible');
                    \App\Models\MovimientoInventario::create([
                        'id_producto' => $lot->id_producto,
                        'id_lote' => $lot->id,
                        'id_usuario' => auth()->id(),
                        'tipo_movimiento' => 'IN',
                        'reference_type' => 'STOCK_ENTRY',
                        'reference_id' => null,
                        'quantity' => (int) $lot->cantidad_disponible,
                        'stock_before' => $prev,
                        'stock_after' => $prev + (int) $lot->cantidad_disponible,
                        'descripcion' => "Nuevo lote {$lot->codigo_lote} registrado desde Lotes.",
                    ]);
                }
            }

            $this->dispatch('toast', type: 'success', message: $message);
            $this->modalVisible = false;
            $this->form->resetForm();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el lote.');
        }
    }

    public function cancel()
    {
        $this->modalVisible = false;
        $this->form->resetForm();
    }

    public function view($id)
    {
        $this->viewingId = $id;
    }

    public function closeView()
    {
        $this->viewingId = null;
    }

    public function delete($id)
    {
        try {
            $lot = Lote::findOrFail($id);
            $lot->estado = $lot->estado ? 0 : 1;
            $lot->save();

            $this->dispatch(
                'toast',
                type: $lot->estado ? 'success' : 'warning',
                message: $lot->estado
                    ? 'Lote activado correctamente.'
                    : 'Lote desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del lote.');
        }
    }
}
