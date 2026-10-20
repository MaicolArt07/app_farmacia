<?php

namespace App\Exports\Sheets;

use App\Models\Lote;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class NearExpirationLotsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function collection()
    {
        $today = now()->toDateString();
        $limitDate = now()->addDays(30)->toDateString();

        return Lote::with('product')
            ->whereDate('fecha_vencimiento', '>=', $today)
            ->whereDate('fecha_vencimiento', '<=', $limitDate)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_vencimiento')
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
            $lot->codigo_lote,
            optional($lot->fecha_vencimiento)->format('d/m/Y'),
            $lot->cantidad_disponible,
        ];
    }

    public function title(): string
    {
        return 'Por Vencer (30 días)';
    }
}
