<?php

namespace App\Livewire;

use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

class LivewireHome extends Component
{
    public $range = '30d';
    public $from = '';
    public $to = '';
    public $hasFilter = false;

    /**
     * Trabajador: sin filtros de fecha; solo ve lo vendido en SU caja abierta
     * del día (misma regla que el listado de ventas). El admin ve todo.
     */
    #[Locked] // no se puede cambiar desde el navegador
    public $workerMode = false;

    public function mount()
    {
        $user = Auth::user();
        $this->workerMode = $user?->hasRole('Trabajador') && !$user?->hasRole('admin');

        $this->applyRange($this->workerMode ? 'today' : '30d');
    }

    protected function workerOpenCash(): ?Caja
    {
        return Caja::where('id_usuario', Auth::id())
            ->where('status', 'OPEN')
            ->where('estado', 1)
            ->first();
    }

    public function setPreset($range)
    {
        if ($this->workerMode) return;

        $this->hasFilter = false;
        $this->applyRange($range);
    }

    public function applyFilter()
    {
        if ($this->workerMode) return;

        if (!$this->from || !$this->to) {
            return;
        }

        $this->range = 'custom';
        $this->hasFilter = true;
    }

    public function clearFilter()
    {
        if ($this->workerMode) return;

        $this->hasFilter = false;
        $this->applyRange('30d');
    }

    private function applyRange($range)
    {
        $this->range = $range;
        $today = Carbon::today();

        [$from, $to] = match ($range) {
            'today' => [$today->copy(), $today->copy()],
            '7d' => [$today->copy()->subDays(6), $today->copy()],
            '30d' => [$today->copy()->subDays(29), $today->copy()],
            'month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'year' => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
            default => [$today->copy()->subDays(29), $today->copy()],
        };

        $this->from = $from->format('Y-m-d');
        $this->to = $to->format('Y-m-d');
    }

    public function render()
    {
        $workerCash = null;
        $user = Auth::user();
        $sucursalId = $user?->id_sucursal;
        $scopedToSucursal = $user && ! $user->can('Super Admin');

        if ($this->workerMode) {
            // Ignora cualquier fecha que llegue del navegador: siempre hoy + su caja abierta.
            $workerCash = $this->workerOpenCash();
            $from = Carbon::today();
            $to = Carbon::today()->endOfDay();

            $scope = fn ($q, $prefix = '') => $q
                ->where($prefix . 'id_caja', $workerCash?->id ?? 0)
                ->whereDate($prefix . 'created_at', $from->toDateString());
        } else {
            $from = Carbon::parse($this->from)->startOfDay();
            $to = Carbon::parse($this->to)->endOfDay();

            $scope = fn ($q, $prefix = '') => $q->whereBetween($prefix . 'fecha_venta', [$from, $to]);
        }

        $salesQuery = $scope(Venta::query())
            ->where('status', '!=', 'CANCELLED')
            ->when($scopedToSucursal, fn ($q) => $q->where('id_sucursal', $sucursalId));

        $salesCount = (clone $salesQuery)->count();
        $salesTotal = (float) (clone $salesQuery)->sum('total');

        $totalProducts = Producto::where('estado', 1)->count();
        $totalClients = Cliente::where('estado', 1)->count();

        $lowStockCount = DB::table('productos')
            ->where('productos.estado', 1)
            ->leftJoin('lotes', function ($join) use ($scopedToSucursal, $sucursalId) {
                $join->on('lotes.id_producto', '=', 'productos.id')->where('lotes.estado', 1);
                if ($scopedToSucursal) {
                    $join->where('lotes.id_sucursal', $sucursalId);
                }
            })
            ->groupBy('productos.id', 'productos.stock_minimo')
            ->havingRaw('COALESCE(SUM(lotes.cantidad_disponible), 0) <= productos.stock_minimo')
            ->get(['productos.id'])
            ->count();

        $topProducts = $scope(DetalleVenta::query()
            ->join('ventas', 'ventas.id', '=', 'detalles_venta.id_venta')
            ->join('productos', 'productos.id', '=', 'detalles_venta.id_producto'), 'ventas.')
            ->where('ventas.status', '!=', 'CANCELLED')
            ->when($scopedToSucursal, fn ($q) => $q->where('ventas.id_sucursal', $sucursalId))
            ->groupBy('productos.id', 'productos.nombre', 'productos.code', 'productos.stock_minimo')
            ->orderByDesc(DB::raw('SUM(detalles_venta.quantity)'))
            ->limit(8)
            ->get([
                'productos.id',
                'productos.nombre as name',
                'productos.code',
                'productos.stock_minimo as minimum_stock',
                DB::raw('SUM(detalles_venta.quantity) as total_quantity'),
                DB::raw('SUM(detalles_venta.subtotal) as total_revenue'),
            ]);

        $maxQuantity = $topProducts->max('total_quantity') ?: 1;

        $topProducts = $topProducts->map(function ($row) use ($maxQuantity, $scopedToSucursal, $sucursalId) {
            $stock = DB::table('lotes')
                ->where('id_producto', $row->id)
                ->where('estado', 1)
                ->when($scopedToSucursal, fn ($q) => $q->where('id_sucursal', $sucursalId))
                ->sum('cantidad_disponible');

            return [
                'name' => $row->name,
                'code' => $row->code,
                'quantity' => (int) $row->total_quantity,
                'revenue' => (float) $row->total_revenue,
                'stock' => (int) $stock,
                'low_stock' => $stock <= $row->minimum_stock,
                'percent' => round(($row->total_quantity / $maxQuantity) * 100),
            ];
        });

        $days = [];
        $cursor = $from->copy();
        while ($cursor->lte($to) && $cursor->diffInDays($from) < 62) {
            $days[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }

        $salesByDayRaw = (clone $salesQuery)
            ->selectRaw('DATE(fecha_venta) as day, SUM(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $salesByDayLabels = collect($days)->map(fn ($d) => Carbon::parse($d)->format('d/m'))->values();
        $salesByDayData = collect($days)->map(fn ($d) => (float) ($salesByDayRaw[$d] ?? 0))->values();

        $salesByPaymentMethod = (clone $salesQuery)
            ->selectRaw('metodo_pago, COUNT(*) as total')
            ->groupBy('metodo_pago')
            ->pluck('total', 'metodo_pago');

        return view('livewire.inicio.index', [
            'workerCash' => $workerCash,
            'periodLabel' => $this->workerMode ? 'en tu caja de hoy' : 'en el periodo',
            'totalProducts' => $totalProducts,
            'totalClients' => $totalClients,
            'salesCount' => $salesCount,
            'salesTotal' => $salesTotal,
            'lowStockCount' => $lowStockCount,
            'topProducts' => $topProducts,
            'salesByDayLabels' => $salesByDayLabels,
            'salesByDayData' => $salesByDayData,
            'paymentMethodLabels' => $salesByPaymentMethod->keys(),
            'paymentMethodData' => $salesByPaymentMethod->values(),
        ]);
    }
}
