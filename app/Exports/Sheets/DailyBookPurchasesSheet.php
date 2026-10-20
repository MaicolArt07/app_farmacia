<?php

namespace App\Exports\Sheets;

use App\Models\Compra;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class DailyBookPurchasesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(private string $from, private string $to)
    {
    }

    public function collection()
    {
        return Compra::with(['supplier.person', 'user'])
            ->whereBetween('fecha_compra', [$this->from, $this->to])
            ->orderBy('fecha_compra')
            ->get();
    }

    public function headings(): array
    {
        return ['Fecha', 'Proveedor', 'N° Factura', 'Usuario', 'Total', 'Estado'];
    }

    public function map($purchase): array
    {
        return [
            optional($purchase->fecha_compra)->format('d/m/Y'),
            $purchase->supplier?->razon_social,
            $purchase->numero_factura,
            $purchase->user?->name,
            (string) $purchase->total,
            $purchase->status === 'CANCELLED' ? 'Anulada' : 'Activa',
        ];
    }

    public function title(): string
    {
        return 'Compras';
    }
}
