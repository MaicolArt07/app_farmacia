<?php

namespace App\Exports\Sheets;

use App\Models\Venta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class DailyBookSalesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(private string $from, private string $to)
    {
    }

    public function collection()
    {
        return Venta::with(['client.person', 'user'])
            ->whereBetween('fecha_venta', [$this->from . ' 00:00:00', $this->to . ' 23:59:59'])
            ->orderBy('fecha_venta')
            ->get();
    }

    public function headings(): array
    {
        return ['Fecha', 'Cliente', 'Vendedor', 'Método de pago', 'Total', 'Estado'];
    }

    public function map($sale): array
    {
        return [
            optional($sale->fecha_venta)->format('d/m/Y H:i'),
            $sale->client
                ? trim(($sale->client->person?->nombre ?? '') . ' ' . ($sale->client->person?->apellido ?? ''))
                : 'Consumidor final',
            $sale->user?->name,
            $sale->metodo_pago,
            (string) $sale->total,
            $sale->status === 'CANCELLED' ? 'Anulada' : 'Activa',
        ];
    }

    public function title(): string
    {
        return 'Ventas';
    }
}
