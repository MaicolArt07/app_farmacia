<?php

namespace App\Livewire;

use App\Models\Caja;
use App\Models\Venta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Todas las ventas de UNA caja, con el detalle de cada una (productos,
 * cantidades, precios, pago, canceladas con su motivo) y un resumen de
 * productos vendidos. Solo administrador.
 */
class LivewireCajaVentas extends Component
{
    #[Locked] public $cajaId;

    public $search = '';
    public $paymentFilter = '';   // '', EFECTIVO, QR, MIXTO
    public $showCancelled = true;
    public $tab = 'ventas';       // ventas | productos

    public function mount(Caja $caja)
    {
        abort_unless(Auth::user()?->hasRole('admin'), 403);
        $this->cajaId = $caja->id;
    }

    public function setPaymentFilter($value)
    {
        $this->paymentFilter = in_array($value, ['EFECTIVO', 'QR', 'MIXTO'], true) ? $value : '';
    }

    /* ---------------- Devolución de productos (solo admin) ---------------- */

    public $returnSaleId = null;
    /** [id_detalle_venta => cantidad a devolver] */
    public $returnQty = [];
    public $returnReason = '';

    public function openReturn($saleId)
    {
        abort_unless(Auth::user()?->hasRole('admin'), 403);

        $sale = Venta::where('id_caja', $this->cajaId)->findOrFail($saleId);
        if ($sale->status !== 'ACTIVE') {
            $this->dispatch('toast', type: 'warning', message: 'Solo se pueden hacer devoluciones de ventas activas.');
            return;
        }

        $this->returnSaleId = $sale->id;
        $this->returnQty = $sale->details()->pluck('id')->mapWithKeys(fn ($id) => [$id => 0])->all();
        $this->returnReason = '';
        $this->resetErrorBag();
    }

    public function closeReturn()
    {
        $this->returnSaleId = null;
        $this->returnQty = [];
        $this->returnReason = '';
        $this->resetErrorBag();
    }

    public function saveReturn()
    {
        abort_unless(Auth::user()?->hasRole('admin'), 403);

        $this->validate(
            ['returnReason' => 'required|string|min:3|max:500'],
            ['returnReason.required' => 'Escriba el motivo de la devolución.', 'returnReason.min' => 'Escriba el motivo de la devolución.']
        );

        $sale = Venta::where('id_caja', $this->cajaId)->findOrFail($this->returnSaleId);
        $qty = collect($this->returnQty)->map(fn ($q) => max(0, (int) $q))->filter()->all();

        if (empty($qty)) {
            $this->addError('returnQty', 'Indique al menos una unidad a devolver.');
            return;
        }

        try {
            $dev = app(\App\Services\DevolucionVenta::class)->apply($sale, $qty, $this->returnReason, Auth::id());
            $this->dispatch('toast', type: 'success', message: 'Devolución registrada: ' . number_format($dev->monto, 2) . ' Bs. Stock y caja actualizados.');
            $this->closeReturn();
        } catch (\Throwable $e) {
            $this->addError('returnQty', $e->getMessage());
        }
    }

    public function render()
    {
        $caja = Caja::with('user')->findOrFail($this->cajaId);
        $b = $caja->cashBreakdown();

        $term = trim((string) $this->search);

        $sales = Venta::with(['details.product.laboratory', 'details.service', 'details.lot', 'user', 'cancelledBy', 'returns.details.product', 'returns.details.service', 'returns.user'])
            ->where('id_caja', $caja->id)
            ->when(!$this->showCancelled, fn ($q) => $q->where('status', 'ACTIVE'))
            ->when($this->paymentFilter !== '', fn ($q) => $q->whereRaw('UPPER(metodo_pago) = ?', [$this->paymentFilter]))
            ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                ->whereHas('details.product', fn ($p) => $p->where('nombre', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")->orWhere('barcode', 'like', "%{$term}%"))
                ->orWhereHas('details.service', fn ($s) => $s->where('nombre', 'like', "%{$term}%"))
                ->when(ctype_digit(ltrim($term, '#')), fn ($q) => $q->orWhere('id', (int) ltrim($term, '#')))))
            ->orderBy('id')
            ->get();

        $active = $sales->where('status', 'ACTIVE');

        // Resumen por producto/servicio (solo ventas activas de lo filtrado).
        $products = DB::table('detalles_venta as d')
            ->join('ventas as v', 'v.id', '=', 'd.id_venta')
            ->leftJoin('productos as p', 'p.id', '=', 'd.id_producto')
            ->leftJoin('servicios as s', 's.id', '=', 'd.id_servicio')
            ->leftJoin('lotes as l', 'l.id', '=', 'd.id_lote')
            ->whereIn('v.id', $active->pluck('id'))
            ->groupBy('d.id_producto', 'd.id_servicio', 'p.nombre', 'p.code', 's.nombre')
            ->selectRaw("COALESCE(p.nombre, CONCAT('SERVICIO: ', s.nombre)) as nombre, p.code,
                SUM(d.quantity) as cantidad, COUNT(DISTINCT d.id_venta) as ventas,
                SUM(d.subtotal) as total, SUM(d.quantity * COALESCE(l.precio_compra, 0)) as costo")
            ->orderByDesc('cantidad')->orderBy('nombre')
            ->get();

        // Ventana de devolución abierta: datos + vista previa del efecto.
        $returnSale = null;
        $returnPreview = null;
        if ($this->returnSaleId) {
            $returnSale = Venta::with(['details.product', 'details.service', 'details.lot'])->find($this->returnSaleId);
            $qty = collect($this->returnQty)->map(fn ($q) => max(0, (int) $q))->filter()->all();
            $returnPreview = $returnSale ? app(\App\Services\DevolucionVenta::class)->preview($returnSale, $qty) : null;
        }

        return view('livewire.caja.ventas', [
            'returnSale' => $returnSale,
            'returnPreview' => $returnPreview,
            'caja' => $caja,
            'b' => $b,
            'sales' => $sales,
            'products' => $products,
            'totals' => [
                'ventas' => $active->count(),
                'canceladas' => $sales->where('status', 'CANCELLED')->count(),
                'efectivo' => round($active->sum(fn ($v) => $v->cashPart()), 2),
                'qr' => round($active->sum(fn ($v) => $v->qrPart()), 2),
                'total' => round((float) $active->sum('total'), 2),
                'unidades' => (int) $products->sum('cantidad'),
            ],
        ]);
    }
}
