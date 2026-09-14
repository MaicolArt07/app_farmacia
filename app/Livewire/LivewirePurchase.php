<?php

namespace App\Livewire;

use App\Models\Lot;
use App\Models\Purchase;
use App\Models\SaleDetail;
use App\Models\InventoryMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class LivewirePurchase extends Component
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
        $query = Purchase::with(['supplier.person', 'user']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', "%{$this->search}%")
                  ->orWhere('purchase_date', 'like', "%{$this->search}%")
                  ->orWhereHas('supplier', function ($sub) {
                      $sub->where('company_name', 'like', "%{$this->search}%")
                          ->orWhere('nit', 'like', "%{$this->search}%");
                  });
            });
        }

        return view('livewire.purchase.index', [
            'purchases' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function cancelPurchase($id)
    {
        DB::beginTransaction();

        try {
            $purchase = Purchase::with('details')
                ->where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($purchase->status === 'CANCELLED') {
                DB::rollBack();
                $this->dispatch('toast', type: 'warning', message: 'La compra ya está anulada.');
                return;
            }

            foreach ($purchase->details as $detail) {
                $lot = Lot::where('id_product', $detail->id_product)
                    ->where('batch_code', $detail->batch_code)
                    ->where('expiration_date', $detail->expiration_date)
                    ->lockForUpdate()
                    ->firstOrFail();

                $wasSold = SaleDetail::where('id_lot', $lot->id)->exists();

                if ($wasSold) {
                    DB::rollBack();
                    $this->dispatch(
                        'toast',
                        type: 'warning',
                        message: 'No se puede anular la compra porque uno o más lotes ya tienen ventas registradas.'
                    );
                    return;
                }

                if ($lot->quantity_available < $detail->quantity) {
                    DB::rollBack();
                    $this->dispatch(
                        'toast',
                        type: 'warning',
                        message: 'No se puede anular la compra porque el stock disponible es menor al ingreso original.'
                    );
                    return;
                }

                $stockBefore = $lot->quantity_available;

                $lot->quantity_in -= (int) $detail->quantity;
                $lot->quantity_available -= (int) $detail->quantity;

                if ($lot->quantity_in < 0) {
                    $lot->quantity_in = 0;
                }

                if ($lot->quantity_available < 0) {
                    $lot->quantity_available = 0;
                }

                $lot->save();

                InventoryMovement::create([
                    'id_product' => $detail->id_product,
                    'id_lot' => $lot->id,
                    'id_user' => auth()->id(),
                    'movement_type' => 'CANCEL',
                    'reference_type' => 'PURCHASE_CANCEL',
                    'reference_id' => $purchase->id,
                    'quantity' => (int) $detail->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $lot->quantity_available,
                    'description' => 'Reversión de stock por anulación de compra #' . $purchase->id,
                ]);
            }

            $purchase->status = 'CANCELLED';
            $purchase->state = 0;
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
            $purchase = Purchase::findOrFail($id);
            $purchase->state = $purchase->state ? 0 : 1;
            $purchase->save();

            $this->dispatch(
                'toast',
                type: $purchase->state ? 'success' : 'warning',
                message: $purchase->state
                    ? 'Compra activada correctamente.'
                    : 'Compra desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado de la compra.');
        }
    }
}
