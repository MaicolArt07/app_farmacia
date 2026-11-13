<?php

namespace App\Http\Controllers;

use App\Exports\DailyBookExport;
use App\Exports\InventoryAlertsExport;
use App\Exports\KardexExport;
use App\Exports\PurchasesExport;
use App\Exports\SalesExport;
use App\Exports\StockExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportController extends Controller
{
    public function dailyBook(Request $request)
    {
        [$from, $to] = $this->validatedRange($request);

        return Excel::download(new DailyBookExport($from, $to), "libro-diario_{$from}_a_{$to}.xlsx");
    }

    public function sales(Request $request)
    {
        [$from, $to] = $this->validatedRange($request);

        return Excel::download(new SalesExport($from, $to), "ventas_{$from}_a_{$to}.xlsx");
    }

    public function purchases(Request $request)
    {
        [$from, $to] = $this->validatedRange($request);

        return Excel::download(new PurchasesExport($from, $to), "compras_{$from}_a_{$to}.xlsx");
    }

    public function kardex(Request $request)
    {
        [$from, $to] = $this->validatedRange($request);

        $productId = $request->integer('product_id') ?: null;

        return Excel::download(new KardexExport($from, $to, $productId), "kardex_{$from}_a_{$to}.xlsx");
    }

    public function stock()
    {
        return Excel::download(new StockExport(), 'stock-actual_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function inventoryAlerts(Request $request)
    {
        // Mismo filtro de búsqueda que la pantalla de Notificaciones (opcional).
        $term = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        return Excel::download(new InventoryAlertsExport($term), 'alertas-inventario_' . now()->format('Y-m-d') . '.xlsx');
    }

    /* ---------- Productos más vendidos (solo admin, ver rutas) ---------- */

    public function topProductsPdf(Request $request)
    {
        [$from, $to] = $this->validatedRange($request);
        $order = $this->topOrder($request);

        $rows = \App\Support\TopProductsReport::rows($from, $to, $order);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.top-products', [
            'rows' => $rows,
            'totals' => \App\Support\TopProductsReport::totals($rows),
            'from' => $from,
            'to' => $to,
            'orderLabel' => \App\Support\TopProductsReport::ORDERS[$order],
        ])->setPaper('letter', 'landscape')
            ->stream("productos-mas-vendidos_{$from}_a_{$to}.pdf");
    }

    public function topProductsExcel(Request $request)
    {
        [$from, $to] = $this->validatedRange($request);

        return Excel::download(
            new \App\Exports\TopProductsExport($from, $to, $this->topOrder($request)),
            "productos-mas-vendidos_{$from}_a_{$to}.xlsx"
        );
    }

    private function topOrder(Request $request): string
    {
        $order = (string) $request->query('order', 'cantidad');

        return array_key_exists($order, \App\Support\TopProductsReport::ORDERS) ? $order : 'cantidad';
    }

    private function validatedRange(Request $request): array
    {
        $data = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ], [
            'from.required' => 'Debe indicar la fecha de inicio.',
            'to.required' => 'Debe indicar la fecha de fin.',
            'to.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
        ]);

        return [$data['from'], $data['to']];
    }
}
