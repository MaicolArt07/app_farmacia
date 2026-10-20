<?php

namespace App\Livewire;

use App\Models\Lote;
use App\Models\Producto;
use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\MovimientoInventario;
use App\Models\DetalleVenta;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DetalleCompra;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Form\CompraForm;

class LivewireCompra extends Component
{
    use WithPagination;

    public $search = '';

    public $mode = 'list';
    public $viewMode = false;

    public CompraForm $form;

    public $supplierSearch = '';
    public $supplierResults = [];
    public $selectedSupplierLabel = '';

    public $productSearch = '';
    public $productResults = [];

    public $details = [];

    protected $paginationTheme = 'tailwind';

    public function mount($purchase = null)
    {
        if (request()->routeIs('compras.create') || request()->routeIs('compras.edit') || request()->routeIs('compras.view')) {
            $this->mode = 'form';
            $this->viewMode = request()->routeIs('compras.view');

            $this->form->resetForm();
            $this->details = [];

            if ($purchase) {
                $purchase = Compra::with(['supplier.person', 'details.product'])->findOrFail($purchase);
                $this->form->fillForm($purchase);

                if ($purchase->supplier) {
                    $this->selectedSupplierLabel = $purchase->supplier->razon_social . ' - ' .
                        trim(($purchase->supplier->person?->nombre ?? '') . ' ' . ($purchase->supplier->person?->apellido ?? ''));
                }

                foreach ($purchase->details as $detail) {
                    $this->details[] = [
                        'id_product' => $detail->id_product,
                        'product_label' => $detail->product?->code . ' - ' . $detail->product?->nombre,
                        'batch_code' => $detail->codigo_lote,
                        'expiration_date' => optional($detail->fecha_vencimiento)->format('Y-m-d'),
                        'quantity' => $detail->quantity,
                        'purchase_price' => (float) $detail->precio_compra,
                        'sale_price' => (float) $detail->precio_venta,
                        'subtotal' => (float) $detail->subtotal,
                    ];
                }
            }
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        if ($this->mode === 'form') {
            return view('livewire.compra.form');
        }

        $query = Compra::with(['supplier.person', 'user']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('numero_factura', 'like', "%{$this->search}%")
                  ->orWhere('fecha_compra', 'like', "%{$this->search}%")
                  ->orWhereHas('supplier', function ($sub) {
                      $sub->where('razon_social', 'like', "%{$this->search}%")
                          ->orWhere('nit', 'like', "%{$this->search}%");
                  });
            });
        }

        return view('livewire.compra.index', [
            'purchases' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function updatedSupplierSearch()
    {
        if (strlen(trim($this->supplierSearch)) < 2) {
            $this->supplierResults = [];
            return;
        }

        $this->supplierResults = Proveedor::with('person')
            ->where('estado', 1)
            ->where(function ($q) {
                $q->where('razon_social', 'like', "%{$this->supplierSearch}%")
                  ->orWhere('nit', 'like', "%{$this->supplierSearch}%")
                  ->orWhereHas('person', function ($sub) {
                      $sub->where('nombre', 'like', "%{$this->supplierSearch}%")
                          ->orWhere('apellido', 'like', "%{$this->supplierSearch}%")
                          ->orWhere('ci', 'like', "%{$this->supplierSearch}%");
                  });
            })
            ->limit(8)
            ->get()
            ->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'label' => $supplier->razon_social . ' - ' .
                        trim(($supplier->person?->nombre ?? '') . ' ' . ($supplier->person?->apellido ?? '')),
                ];
            })
            ->toArray();
    }

    public function selectSupplier($id)
    {
        $supplier = Proveedor::with('person')->findOrFail($id);

        $this->form->id_supplier = $supplier->id;
        $this->selectedSupplierLabel = $supplier->razon_social . ' - ' .
            trim(($supplier->person?->nombre ?? '') . ' ' . ($supplier->person?->apellido ?? ''));

        $this->supplierSearch = '';
        $this->supplierResults = [];
    }

    public function clearSupplier()
    {
        $this->form->id_supplier = null;
        $this->selectedSupplierLabel = '';
        $this->supplierSearch = '';
        $this->supplierResults = [];
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
                    'sale_price' => (float) $product->precio_venta,
                ];
            })
            ->toArray();
    }

    public function addProductToDetail($id)
    {
        $product = Producto::findOrFail($id);

        $this->details[] = [
            'id_product' => $product->id,
            'product_label' => $product->code . ' - ' . $product->nombre . ($product->concentracion ? ' | ' . $product->concentracion : ''),
            'batch_code' => '',
            'expiration_date' => '',
            'quantity' => 1,
            'purchase_price' => 0,
            'sale_price' => (float) $product->precio_venta,
            'subtotal' => 0,
        ];

        $this->productSearch = '';
        $this->productResults = [];

        $this->calculateTotals();
    }

    public function removeDetail($index)
    {
        unset($this->details[$index]);
        $this->details = array_values($this->details);
        $this->calculateTotals();
    }

    public function updatedDetails()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $subtotal = 0;

        foreach ($this->details as $i => $detail) {
            $qty = (int) ($detail['quantity'] ?? 0);
            $price = (float) ($detail['purchase_price'] ?? 0);

            $lineSubtotal = $qty * $price;
            $this->details[$i]['subtotal'] = $lineSubtotal;

            $subtotal += $lineSubtotal;
        }

        $this->form->subtotal = $subtotal;
        $this->form->total = $subtotal;
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        if (count($this->details) === 0) {
            $this->dispatch('toast', type: 'warning', message: 'Debe agregar al menos un producto al detalle.');
            return;
        }

        foreach ($this->details as $detail) {
            if (
                empty($detail['id_product']) ||
                empty($detail['batch_code']) ||
                empty($detail['expiration_date']) ||
                (int)$detail['quantity'] <= 0 ||
                (float)$detail['purchase_price'] < 0 ||
                (float)$detail['sale_price'] < 0
            ) {
                $this->dispatch('toast', type: 'warning', message: 'Revise el detalle de compra. Hay filas incompletas o inválidas.');
                return;
            }
        }

        DB::beginTransaction();

        try {
            $this->calculateTotals();

            if ($this->form->id) {
                $purchase = Compra::findOrFail($this->form->id);

                $purchase->update([
                    'id_supplier' => $this->form->id_supplier,
                    'id_user' => Auth::id(),
                    'numero_factura' => $this->form->numero_factura ?: null,
                    'fecha_compra' => $this->form->fecha_compra,
                    'subtotal' => $this->form->subtotal,
                    'total' => $this->form->total,
                    'observacion' => $this->form->observacion ?: null,
                    'estado' => $this->form->estado,
                ]);

                $existingDetails = DetalleCompra::where('id_purchase', $purchase->id)->get();

                foreach ($existingDetails as $existingDetail) {
                    $existingLot = Lote::where('id_product', $existingDetail->id_product)
                        ->where('codigo_lote', $existingDetail->codigo_lote)
                        ->where('fecha_vencimiento', $existingDetail->fecha_vencimiento)
                        ->lockForUpdate()
                        ->first();

                    if (!$existingLot) {
                        continue;
                    }

                    $wasSold = DetalleVenta::where('id_lot', $existingLot->id)->exists();

                    if ($wasSold) {
                        DB::rollBack();
                        $this->dispatch(
                            'toast',
                            type: 'warning',
                            message: 'No se puede editar la compra porque uno o más lotes ya tienen ventas registradas. Anule la compra y registre una nueva.'
                        );
                        return;
                    }

                    if ($existingLot->cantidad_disponible < $existingDetail->quantity) {
                        DB::rollBack();
                        $this->dispatch(
                            'toast',
                            type: 'warning',
                            message: 'No se puede editar la compra porque el stock disponible es menor al ingreso original de uno de los lotes.'
                        );
                        return;
                    }

                    $revertStockBefore = $existingLot->cantidad_disponible;

                    $existingLot->cantidad_ingresada -= (int) $existingDetail->quantity;
                    $existingLot->cantidad_disponible -= (int) $existingDetail->quantity;

                    if ($existingLot->cantidad_ingresada < 0) {
                        $existingLot->cantidad_ingresada = 0;
                    }

                    if ($existingLot->cantidad_disponible < 0) {
                        $existingLot->cantidad_disponible = 0;
                    }

                    $existingLot->save();

                    MovimientoInventario::create([
                        'id_product' => $existingDetail->id_product,
                        'id_lot' => $existingLot->id,
                        'id_user' => Auth::id(),
                        'tipo_movimiento' => 'CANCEL',
                        'reference_type' => 'PURCHASE_EDIT',
                        'reference_id' => $purchase->id,
                        'quantity' => (int) $existingDetail->quantity,
                        'stock_before' => $revertStockBefore,
                        'stock_after' => $existingLot->cantidad_disponible,
                        'descripcion' => 'Reversión de stock por edición de compra #' . $purchase->id,
                    ]);
                }

                DetalleCompra::where('id_purchase', $purchase->id)->delete();
            } else {
                $purchase = Compra::create([
                    'id_supplier' => $this->form->id_supplier,
                    'id_user' => Auth::id(),
                    'numero_factura' => $this->form->numero_factura ?: null,
                    'fecha_compra' => $this->form->fecha_compra,
                    'subtotal' => $this->form->subtotal,
                    'total' => $this->form->total,
                    'observacion' => $this->form->observacion ?: null,
                    'estado' => $this->form->estado,
                ]);
            }

            foreach ($this->details as $detail) {
                DetalleCompra::create([
                    'id_purchase' => $purchase->id,
                    'id_product' => $detail['id_product'],
                    'codigo_lote' => $detail['batch_code'],
                    'fecha_vencimiento' => $detail['expiration_date'],
                    'quantity' => $detail['quantity'],
                    'precio_compra' => $detail['purchase_price'],
                    'precio_venta' => $detail['sale_price'],
                    'subtotal' => $detail['subtotal'],
                ]);

                $lot = Lote::where('id_product', $detail['id_product'])
                    ->where('codigo_lote', $detail['batch_code'])
                    ->where('fecha_vencimiento', $detail['expiration_date'])
                    ->lockForUpdate()
                    ->first();

                if ($lot) {
                    $stockBefore = $lot->cantidad_disponible;

                    $lot->cantidad_ingresada += (int) $detail['quantity'];
                    $lot->cantidad_disponible += (int) $detail['quantity'];
                    $lot->precio_compra = $detail['purchase_price'];
                    $lot->precio_venta = $detail['sale_price'];
                    $lot->estado = 1;
                    $lot->save();

                    $stockAfter = $lot->cantidad_disponible;
                } else {
                    $stockBefore = 0;

                    $lot = Lote::create([
                        'id_product' => $detail['id_product'],
                        'codigo_lote' => $detail['batch_code'],
                        'precio_compra' => $detail['purchase_price'],
                        'precio_venta' => $detail['sale_price'],
                        'cantidad_ingresada' => $detail['quantity'],
                        'cantidad_disponible' => $detail['quantity'],
                        'fecha_vencimiento' => $detail['expiration_date'],
                        'ubicacion' => null,
                        'estado' => 1,
                    ]);

                    $stockAfter = $lot->cantidad_disponible;
                }

                MovimientoInventario::create([
                    'id_product' => $detail['id_product'],
                    'id_lot' => $lot->id,
                    'id_user' => Auth::id(),
                    'tipo_movimiento' => 'IN',
                    'reference_type' => 'PURCHASE',
                    'reference_id' => $purchase->id,
                    'quantity' => (int) $detail['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'descripcion' => 'Ingreso por compra #' . $purchase->id,
                ]);
            }

            DB::commit();

            $this->dispatch('toast', type: 'success', message: $this->form->id ? 'Compra actualizada correctamente.' : 'Compra registrada correctamente.');

            return redirect()->route('compras');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar la compra.');
        }
    }

    public function cancel()
    {
        return redirect()->route('compras');
    }

    public function cancelPurchase($id)
    {
        DB::beginTransaction();

        try {
            $purchase = Compra::with('details')
                ->where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($purchase->status === 'CANCELLED') {
                DB::rollBack();
                $this->dispatch('toast', type: 'warning', message: 'La compra ya está anulada.');
                return;
            }

            foreach ($purchase->details as $detail) {
                $lot = Lote::where('id_product', $detail->id_product)
                    ->where('codigo_lote', $detail->codigo_lote)
                    ->where('fecha_vencimiento', $detail->fecha_vencimiento)
                    ->lockForUpdate()
                    ->firstOrFail();

                $wasSold = DetalleVenta::where('id_lot', $lot->id)->exists();

                if ($wasSold) {
                    DB::rollBack();
                    $this->dispatch(
                        'toast',
                        type: 'warning',
                        message: 'No se puede anular la compra porque uno o más lotes ya tienen ventas registradas.'
                    );
                    return;
                }

                if ($lot->cantidad_disponible < $detail->quantity) {
                    DB::rollBack();
                    $this->dispatch(
                        'toast',
                        type: 'warning',
                        message: 'No se puede anular la compra porque el stock disponible es menor al ingreso original.'
                    );
                    return;
                }

                $stockBefore = $lot->cantidad_disponible;

                $lot->cantidad_ingresada -= (int) $detail->quantity;
                $lot->cantidad_disponible -= (int) $detail->quantity;

                if ($lot->cantidad_ingresada < 0) {
                    $lot->cantidad_ingresada = 0;
                }

                if ($lot->cantidad_disponible < 0) {
                    $lot->cantidad_disponible = 0;
                }

                $lot->save();

                MovimientoInventario::create([
                    'id_product' => $detail->id_product,
                    'id_lot' => $lot->id,
                    'id_user' => auth()->id(),
                    'tipo_movimiento' => 'CANCEL',
                    'reference_type' => 'PURCHASE_CANCEL',
                    'reference_id' => $purchase->id,
                    'quantity' => (int) $detail->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $lot->cantidad_disponible,
                    'descripcion' => 'Reversión de stock por anulación de compra #' . $purchase->id,
                ]);
            }

            $purchase->status = 'CANCELLED';
            $purchase->estado = 0;
            $purchase->cancelled_at = now();
            $purchase->cancelled_by = auth()->id();
            $purchase->cancel_reason = 'Compra anulada desde el módulo de compras.';
            $purchase->save();

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Compra anulada y stock revertido correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('toast', type: 'error', message: 'No se pudo anular la compra. ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $purchase = Compra::findOrFail($id);
            $purchase->estado = $purchase->estado ? 0 : 1;
            $purchase->save();

            $this->dispatch(
                'toast',
                type: $purchase->estado ? 'success' : 'warning',
                message: $purchase->estado
                    ? 'Compra activada correctamente.'
                    : 'Compra desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado de la compra.');
        }
    }
}
