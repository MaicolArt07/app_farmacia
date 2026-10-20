<?php

namespace App\Exports;

use App\Models\Compra;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchasesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
        return ['Fecha', 'Proveedor', 'N° Factura', 'Usuario', 'Subtotal', 'Total', 'Estado'];
    }

    public function map($purchase): array
    {
        // string: evita que PhpSpreadsheet trate un monto igual a 0 como
        // null y deje la celda vacía.
        return [
            optional($purchase->fecha_compra)->format('d/m/Y'),
            $purchase->supplier?->razon_social,
            $purchase->numero_factura,
            $purchase->user?->name,
            (string) $purchase->subtotal,
            (string) $purchase->total,
            $purchase->status === 'CANCELLED' ? 'Anulada' : 'Activa',
        ];
    }
}
