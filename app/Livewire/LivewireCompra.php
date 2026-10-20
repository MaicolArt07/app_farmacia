<?php

namespace App\Livewire;

use App\Models\Lote;
use App\Models\Compra;
use App\Models\DetalleVenta;
use App\Models\MovimientoInventario;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class LivewireCompra extends Component
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
