<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
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

        $salesQuery = Sale::whereBetween('sale_date', [$from, $to])
            ->where('status', '!=', 'CANCELLED');

        $salesCount = (clone $salesQuery)->count();
        $salesTotal = (float) (clone $salesQuery)->sum('total');

        $totalProducts = Product::where('state', 1)->count();
        $totalClients = Client::where('state', 1)->count();

        $lowStockCount = DB::table('products')
            ->where('products.state', 1)
            ->leftJoin('lots', function ($join) {
                $join->on('lots.id_product', '=', 'products.id')->where('lots.state', 1);
            })
            ->groupBy('products.id', 'products.minimum_stock')
            ->havingRaw('COALESCE(SUM(lots.quantity_available), 0) <= products.minimum_stock')
            ->get(['products.id'])
            ->count();

        $topProducts = SaleDetail::query()
            ->join('sales', 'sales.id', '=', 'sale_details.id_sale')
            ->join('products', 'products.id', '=', 'sale_details.id_product')
            ->whereBetween('sales.sale_date', [$from, $to])
            ->where('sales.status', '!=', 'CANCELLED')
            ->groupBy('products.id', 'products.name', 'products.code', 'products.minimum_stock')
            ->orderByDesc(DB::raw('SUM(sale_details.quantity)'))
            ->limit(8)
            ->get([
                'products.id',
                'products.name',
                'products.code',
                'products.minimum_stock',
                DB::raw('SUM(sale_details.quantity) as total_quantity'),
                DB::raw('SUM(sale_details.subtotal) as total_revenue'),
            ]);

        $maxQuantity = $topProducts->max('total_quantity') ?: 1;

        $topProducts = $topProducts->map(function ($row) use ($maxQuantity) {
            $stock = DB::table('lots')
                ->where('id_product', $row->id)
                ->where('state', 1)
                ->sum('quantity_available');

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
            ->selectRaw('DATE(sale_date) as day, SUM(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $salesByDayLabels = collect($days)->map(fn ($d) => Carbon::parse($d)->format('d/m'))->values();
        $salesByDayData = collect($days)->map(fn ($d) => (float) ($salesByDayRaw[$d] ?? 0))->values();

        $salesByPaymentMethod = (clone $salesQuery)
            ->selectRaw('payment_method, COUNT(*) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

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
