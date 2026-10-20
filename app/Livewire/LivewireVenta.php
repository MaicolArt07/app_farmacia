<?php

namespace App\Livewire;

use App\Models\Venta;
use App\Models\Lote;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\Facturacion\FacturacionServiceInterface;

class LivewireVenta extends Component
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
        $query = Venta::with(['client.person', 'user', 'invoice']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('fecha_venta', 'like', "%{$this->search}%")
                    ->orWhere('metodo_pago', 'like', "%{$this->search}%")
                    ->orWhereHas('client.person', function ($sub) {
                        $sub->where('nombre', 'like', "%{$this->search}%")
                            ->orWhere('apellido', 'like', "%{$this->search}%")
                            ->orWhere('ci', 'like', "%{$this->search}%");
                    });
            });
        }

        $openCash = Caja::where('id_user', Auth::id())
            ->where('status', 'OPEN')
            ->where('estado', 1)
            ->first();

        return view('livewire.venta.index', [
            'sales' => $query->orderBy('id', 'desc')->paginate(10),
            'openCash' => $openCash,
            'cashExpired' => $openCash ? $openCash->isExpired() : false,
        ]);
    }

    public function extendCash()
    {
        $openCash = Caja::where('id_user', Auth::id())
            ->where('status', 'OPEN')
            ->where('estado', 1)
            ->first();

        if (!$openCash) {
            return;
        }

        $openCash->extend();

        $this->dispatch('toast', type: 'success', message: 'Vigencia de la caja ampliada correctamente.');
    }

    public function generarFactura($id)
    {
        try {
            $sale = Venta::with(['client.person', 'invoice'])->findOrFail($id);

            if ($sale->status === 'CANCELLED') {
                $this->dispatch('toast', type: 'warning', message: 'No se puede facturar una venta anulada.');
                return;
            }

            if ($sale->invoice && in_array($sale->invoice->estado, ['ENVIADA'], true)) {
                $this->dispatch('toast', type: 'warning', message: 'Esta venta ya tiene una factura generada.');
                return;
            }

            app(FacturacionServiceInterface::class)->generar($sale);

            $this->dispatch('toast', type: 'success', message: 'Factura generada correctamente.');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo generar la factura. ' . $e->getMessage());
        }
    }

    public function cancelSale($id)
    {
        DB::beginTransaction();

        try {
            $sale = Venta::with(['details.lot', 'invoice'])
                ->where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($sale->status === 'CANCELLED') {
                DB::rollBack();
                $this->dispatch('toast', type: 'warning', message: 'La venta ya está anulada.');
                return;
            }

            if ($sale->invoice && $sale->invoice->estado === 'ENVIADA') {
                DB::rollBack();
                $this->dispatch(
                    'toast',
                    type: 'warning',
                    message: 'Esta venta tiene una factura generada. Anule la factura desde el módulo de Facturación antes de anular la venta.'
                );
                return;
            }

            foreach ($sale->details as $detail) {
                $lot = Lote::where('id', $detail->id_lot)
                    ->lockForUpdate()
                    ->firstOrFail();

                $stockBefore = $lot->cantidad_disponible;

                $lot->cantidad_disponible += $detail->quantity;
                $lot->save();

                MovimientoInventario::create([
                    'id_product' => $detail->id_product,
                    'id_lot' => $lot->id,
                    'id_user' => Auth::id(),
                    'tipo_movimiento' => 'RETURN',
                    'reference_type' => 'SALE_CANCEL',
                    'reference_id' => $sale->id,
                    'quantity' => $detail->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $lot->cantidad_disponible,
                    'descripcion' => 'Devolución de stock por anulación de venta #' . $sale->id,
                ]);
            }

            $cash = Caja::where('id_user', Auth::id())
                ->where('status', 'OPEN')
                ->where('estado', 1)
                ->lockForUpdate()
                ->first();

            if ($cash) {
                MovimientoCaja::create([
                    'id_cash_register' => $cash->id,
                    'id_user' => Auth::id(),
                    'type' => 'EXPENSE',
                    'concepto' => 'Anulación venta #' . $sale->id,
                    'monto' => $sale->total,
                    'observacion' => 'Reversión automática por anulación de venta.',
                ]);

                $cash->monto_esperado -= $sale->total;
                $cash->save();
            }

            $sale->status = 'CANCELLED';
            $sale->estado = 0;
            $sale->cancelled_at = now();
            $sale->cancelled_by = Auth::id();
            $sale->cancel_reason = 'Venta anulada desde el módulo de ventas.';
            $sale->save();

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Venta anulada, stock restaurado y caja ajustada.');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo anular la venta. ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $sale = Venta::findOrFail($id);
            $sale->estado = $sale->estado ? 0 : 1;
            $sale->save();

            $this->dispatch(
                'toast',
                type: $sale->estado ? 'success' : 'warning',
                message: $sale->estado
                    ? 'Venta activada correctamente.'
                    : 'Venta desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado de la venta.');
        }
    }
}
