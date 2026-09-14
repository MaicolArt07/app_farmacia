<?php

namespace App\Exports\Sheets;

use App\Models\Lot;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExpiredLotsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function collection()
    {
        return Lot::with('product')
            ->whereDate('expiration_date', '<', now()->toDateString())
            ->where('quantity_available', '>', 0)
            ->orderBy('expiration_date')
            ->get();
    }

    public function headings(): array
    {
        return ['Producto', 'Lote', 'Fecha de vencimiento', 'Stock disponible'];
    }

    public function map($lot): array
    {
        return [
            $lot->product?->name,
            $lot->batch_code,
            optional($lot->expiration_date)->format('d/m/Y'),
            $lot->quantity_available,
        ];
    }

    public function title(): string
    {
        return 'Vencidos';
    }
}
