<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LivewireHome extends Component
{
    public $range = '30d';
    public $from = '';
    public $to = '';
    public $hasFilter = false;

    public function mount()
    {
        $this->applyRange('30d');
    }

    public function setPreset($range)
    {
        $this->hasFilter = false;
        $this->applyRange($range);
    }

    public function applyFilter()
    {
        if (!$this->from || !$this->to) {
            return;
        }

        $this->range = 'custom';
        $this->hasFilter = true;
    }

    public function clearFilter()
    {
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
        $from = Carbon::parse($this->from)->startOfDay();
        $to = Carbon::parse($this->to)->endOfDay();

        $salesQuery = Venta::whereBetween('fecha_venta', [$from, $to])
            ->where('status', '!=', 'CANCELLED');

        $salesCount = (clone $salesQuery)->count();
        $salesTotal = (float) (clone $salesQuery)->sum('total');

        $totalProducts = Producto::where('estado', 1)->count();
        $totalClients = Cliente::where('estado', 1)->count();

        $lowStockCount = DB::table('productos')
            ->where('productos.estado', 1)
            ->leftJoin('lotes', function ($join) {
                $join->on('lotes.id_product', '=', 'productos.id')->where('lotes.estado', 1);
            })
            ->groupBy('productos.id', 'productos.stock_minimo')
            ->havingRaw('COALESCE(SUM(lotes.cantidad_disponible), 0) <= productos.stock_minimo')
            ->get(['productos.id'])
            ->count();

        $topProducts = DetalleVenta::query()
            ->join('ventas', 'ventas.id', '=', 'detalles_venta.id_sale')
            ->join('productos', 'productos.id', '=', 'detalles_venta.id_product')
            ->whereBetween('ventas.fecha_venta', [$from, $to])
            ->where('ventas.status', '!=', 'CANCELLED')
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

        $topProducts = $topProducts->map(function ($row) use ($maxQuantity) {
            $stock = DB::table('lotes')
                ->where('id_product', $row->id)
                ->where('estado', 1)
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
