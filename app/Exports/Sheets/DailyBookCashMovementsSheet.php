<?php

namespace App\Exports\Sheets;

use App\Models\MovimientoCaja;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class DailyBookCashMovementsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(private string $from, private string $to)
    {
    }

    public function collection()
    {
        return MovimientoCaja::with(['cashRegister', 'user'])
            ->whereHas('cashRegister', function ($q) {
                $q->whereBetween('fecha_apertura', [$this->from, $this->to]);
            })
            ->orderBy('created_at')
            ->get();
    }

    public function headings(): array
    {
        return ['Fecha', 'Caja (usuario)', 'Tipo', 'Concepto', 'Monto', 'Registrado por', 'Observación'];
    }

    public function map($movement): array
    {
        return [
            optional($movement->created_at)->format('d/m/Y H:i'),
            $movement->cashRegister?->user?->name,
            $movement->type === 'INCOME' ? 'Ingreso' : 'Egreso',
            $movement->concepto,
            (string) $movement->monto,
            $movement->user?->name,
            $movement->observacion,
        ];
    }

    public function title(): string
    {
        return 'Movimientos de Caja';
    }
}
