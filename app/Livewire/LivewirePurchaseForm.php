<?php

namespace App\Livewire;

use App\Models\Lot;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\InventoryMovement;
use App\Models\SaleDetail;
use Livewire\Component;
use App\Models\PurchaseDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Form\PurchaseForm;

class LivewirePurchaseForm extends Component
{
    public PurchaseForm $form;

    public $viewMode = false;

    public $supplierSearch = '';
    public $supplierResults = [];
    public $selectedSupplierLabel = '';

    public $productSearch = '';
    public $productResults = [];

    public $details = [];

    public function mount($purchase = null)
    {
        $this->form->resetForm();
        $this->details = [];
        $this->viewMode = request()->routeIs('purchases.view');

        if ($purchase) {
            $purchase = Purchase::with(['supplier.person', 'details.product'])->findOrFail($purchase);
            $this->form->fillForm($purchase);

            if ($purchase->supplier) {
                $this->selectedSupplierLabel = $purchase->supplier->company_name . ' - ' .
                    trim(($purchase->supplier->person?->name ?? '') . ' ' . ($purchase->supplier->person?->lastname ?? ''));
            }

            foreach ($purchase->details as $detail) {
                $this->details[] = [
                    'id_product' => $detail->id_product,
                    'product_label' => $detail->product?->code . ' - ' . $detail->product?->name,
                    'batch_code' => $detail->batch_code,
                    'expiration_date' => optional($detail->expiration_date)->format('Y-m-d'),
                    'quantity' => $detail->quantity,
                    'purchase_price' => (float) $detail->purchase_price,
                    'sale_price' => (float) $detail->sale_price,
                    'subtotal' => (float) $detail->subtotal,
                ];
            }
        }
    }

    public function updatedSupplierSearch()
    {
        if (strlen(trim($this->supplierSearch)) < 2) {
            $this->supplierResults = [];
            return;
        }

        $this->supplierResults = Supplier::with('person')
            ->where('state', 1)
            ->where(function ($q) {
                $q->where('company_name', 'like', "%{$this->supplierSearch}%")
                  ->orWhere('nit', 'like', "%{$this->supplierSearch}%")
                  ->orWhereHas('person', function ($sub) {
                      $sub->where('name', 'like', "%{$this->supplierSearch}%")
                          ->orWhere('lastname', 'like', "%{$this->supplierSearch}%")
                          ->orWhere('ci', 'like', "%{$this->supplierSearch}%");
                  });
            })
            ->limit(8)
            ->get()
            ->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'label' => $supplier->company_name . ' - ' .
                        trim(($supplier->person?->name ?? '') . ' ' . ($supplier->person?->lastname ?? '')),
                ];
            })
            ->toArray();
    }

    public function selectSupplier($id)
    {
        $supplier = Supplier::with('person')->findOrFail($id);

        $this->form->id_supplier = $supplier->id;
        $this->selectedSupplierLabel = $supplier->company_name . ' - ' .
            trim(($supplier->person?->name ?? '') . ' ' . ($supplier->person?->lastname ?? ''));

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

        $this->productResults = Product::query()
            ->where('state', 1)
            ->where(function ($q) {
                $q->where('code', 'like', "%{$this->productSearch}%")
                  ->orWhere('barcode', 'like', "%{$this->productSearch}%")
                  ->orWhere('name', 'like', "%{$this->productSearch}%")
                  ->orWhere('generic_name', 'like', "%{$this->productSearch}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'label' => $product->code . ' - ' . $product->name .
                        ($product->concentration ? ' | ' . $product->concentration : ''),
                    'sale_price' => (float) $product->sale_price,
                ];
            })
            ->toArray();
    }

    public function addProductToDetail($id)
    {
        $product = Product::findOrFail($id);

        $this->details[] = [
            'id_product' => $product->id,
            'product_label' => $product->code . ' - ' . $product->name . ($product->concentration ? ' | ' . $product->concentration : ''),
            'batch_code' => '',
            'expiration_date' => '',
            'quantity' => 1,
            'purchase_price' => 0,
            'sale_price' => (float) $product->sale_price,
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
                $purchase = Purchase::findOrFail($this->form->id);

                $purchase->update([
                    'id_supplier' => $this->form->id_supplier,
                    'id_user' => Auth::id(),
                    'invoice_number' => $this->form->invoice_number ?: null,
                    'purchase_date' => $this->form->purchase_date,
                    'subtotal' => $this->form->subtotal,
                    'total' => $this->form->total,
                    'observation' => $this->form->observation ?: null,
                    'state' => $this->form->state,
                ]);

                $existingDetails = PurchaseDetail::where('id_purchase', $purchase->id)->get();

                foreach ($existingDetails as $existingDetail) {
                    $existingLot = Lot::where('id_product', $existingDetail->id_product)
                        ->where('batch_code', $existingDetail->batch_code)
                        ->where('expiration_date', $existingDetail->expiration_date)
                        ->lockForUpdate()
                        ->first();

                    if (!$existingLot) {
                        continue;
                    }

                    $wasSold = SaleDetail::where('id_lot', $existingLot->id)->exists();

                    if ($wasSold) {
                        DB::rollBack();
                        $this->dispatch(
                            'toast',
                            type: 'warning',
                            message: 'No se puede editar la compra porque uno o más lotes ya tienen ventas registradas. Anule la compra y registre una nueva.'
                        );
                        return;
                    }

                    if ($existingLot->quantity_available < $existingDetail->quantity) {
                        DB::rollBack();
                        $this->dispatch(
                            'toast',
                            type: 'warning',
                            message: 'No se puede editar la compra porque el stock disponible es menor al ingreso original de uno de los lotes.'
                        );
                        return;
                    }

                    $revertStockBefore = $existingLot->quantity_available;

                    $existingLot->quantity_in -= (int) $existingDetail->quantity;
                    $existingLot->quantity_available -= (int) $existingDetail->quantity;

                    if ($existingLot->quantity_in < 0) {
                        $existingLot->quantity_in = 0;
                    }

                    if ($existingLot->quantity_available < 0) {
                        $existingLot->quantity_available = 0;
                    }

                    $existingLot->save();

                    InventoryMovement::create([
                        'id_product' => $existingDetail->id_product,
                        'id_lot' => $existingLot->id,
                        'id_user' => Auth::id(),
                        'movement_type' => 'CANCEL',
                        'reference_type' => 'PURCHASE_EDIT',
                        'reference_id' => $purchase->id,
                        'quantity' => (int) $existingDetail->quantity,
                        'stock_before' => $revertStockBefore,
                        'stock_after' => $existingLot->quantity_available,
                        'description' => 'Reversión de stock por edición de compra #' . $purchase->id,
                    ]);
                }

                PurchaseDetail::where('id_purchase', $purchase->id)->delete();
            } else {
                $purchase = Purchase::create([
                    'id_supplier' => $this->form->id_supplier,
                    'id_user' => Auth::id(),
                    'invoice_number' => $this->form->invoice_number ?: null,
                    'purchase_date' => $this->form->purchase_date,
                    'subtotal' => $this->form->subtotal,
                    'total' => $this->form->total,
                    'observation' => $this->form->observation ?: null,
                    'state' => $this->form->state,
                ]);
            }

            foreach ($this->details as $detail) {
                PurchaseDetail::create([
                    'id_purchase' => $purchase->id,
                    'id_product' => $detail['id_product'],
                    'batch_code' => $detail['batch_code'],
                    'expiration_date' => $detail['expiration_date'],
                    'quantity' => $detail['quantity'],
                    'purchase_price' => $detail['purchase_price'],
                    'sale_price' => $detail['sale_price'],
                    'subtotal' => $detail['subtotal'],
                ]);

                $lot = Lot::where('id_product', $detail['id_product'])
                    ->where('batch_code', $detail['batch_code'])
                    ->where('expiration_date', $detail['expiration_date'])
                    ->lockForUpdate()
                    ->first();

                if ($lot) {
                    $stockBefore = $lot->quantity_available;

                    $lot->quantity_in += (int) $detail['quantity'];
                    $lot->quantity_available += (int) $detail['quantity'];
                    $lot->purchase_price = $detail['purchase_price'];
                    $lot->sale_price = $detail['sale_price'];
                    $lot->state = 1;
                    $lot->save();

                    $stockAfter = $lot->quantity_available;
                } else {
                    $stockBefore = 0;

                    $lot = Lot::create([
                        'id_product' => $detail['id_product'],
                        'batch_code' => $detail['batch_code'],
                        'purchase_price' => $detail['purchase_price'],
                        'sale_price' => $detail['sale_price'],
                        'quantity_in' => $detail['quantity'],
                        'quantity_available' => $detail['quantity'],
                        'expiration_date' => $detail['expiration_date'],
                        'location' => null,
                        'state' => 1,
                    ]);

                    $stockAfter = $lot->quantity_available;
                }

                InventoryMovement::create([
                    'id_product' => $detail['id_product'],
                    'id_lot' => $lot->id,
                    'id_user' => Auth::id(),
                    'movement_type' => 'IN',
                    'reference_type' => 'PURCHASE',
                    'reference_id' => $purchase->id,
                    'quantity' => (int) $detail['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'description' => 'Ingreso por compra #' . $purchase->id,
                ]);
            }

            DB::commit();

            $this->dispatch('toast', type: 'success', message: $this->form->id ? 'Compra actualizada correctamente.' : 'Compra registrada correctamente.');

            return redirect()->route('purchases');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar la compra.');
        }
    }

    public function cancel()
    {
        return redirect()->route('purchases');
    }

    public function render()
    {
        return view('livewire.purchase.form');
    }
}
