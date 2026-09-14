<?php

namespace App\Exports\Sheets;

use App\Models\Purchase;
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
        return Purchase::with(['supplier.person', 'user'])
            ->whereBetween('purchase_date', [$this->from, $this->to])
            ->orderBy('purchase_date')
            ->get();
    }

    public function headings(): array
    {
        return ['Fecha', 'Proveedor', 'N° Factura', 'Usuario', 'Total', 'Estado'];
    }

    public function map($purchase): array
    {
        return [
            optional($purchase->purchase_date)->format('d/m/Y'),
            $purchase->supplier?->company_name,
            $purchase->invoice_number,
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
