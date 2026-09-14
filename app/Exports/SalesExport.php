<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private string $from, private string $to)
    {
    }

    public function collection()
    {
        return Sale::with(['client.person', 'user'])
            ->whereBetween('sale_date', [$this->from . ' 00:00:00', $this->to . ' 23:59:59'])
            ->orderBy('sale_date')
            ->get();
    }

    public function headings(): array
    {
        return ['Fecha', 'Cliente', 'Vendedor', 'Método de pago', 'Subtotal', 'Descuento', 'Total', 'Estado'];
    }

    public function map($sale): array
    {
        // Los montos se envían como string: PhpSpreadsheet trata un número
        // igual a 0 como equivalente a null (comparación floja) y deja la
        // celda vacía en vez de mostrar "0".
        return [
            optional($sale->sale_date)->format('d/m/Y H:i'),
            $sale->client
                ? trim(($sale->client->person?->name ?? '') . ' ' . ($sale->client->person?->lastname ?? ''))
                : 'Consumidor final',
            $sale->user?->name,
            $sale->payment_method,
            (string) $sale->subtotal,
            (string) $sale->discount,
            (string) $sale->total,
            $sale->status === 'CANCELLED' ? 'Anulada' : 'Activa',
        ];
    }
}
