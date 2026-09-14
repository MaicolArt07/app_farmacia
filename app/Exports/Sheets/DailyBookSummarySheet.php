<?php

namespace App\Exports\Sheets;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Purchase;
use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class DailyBookSummarySheet implements FromArray, WithTitle, ShouldAutoSize
{
    public function __construct(private string $from, private string $to)
    {
    }

    public function array(): array
    {
        $sales = Sale::where('status', 'ACTIVE')
            ->whereBetween('sale_date', [$this->from . ' 00:00:00', $this->to . ' 23:59:59'])
            ->get();

        $purchases = Purchase::where('status', 'ACTIVE')
            ->whereBetween('purchase_date', [$this->from, $this->to])
            ->get();

        $movements = CashMovement::whereHas('cashRegister', function ($q) {
            $q->whereBetween('opening_date', [$this->from, $this->to]);
        })->get();

        $income = $movements->where('type', 'INCOME')->sum('amount');
        $expense = $movements->where('type', 'EXPENSE')->sum('amount');

        $registers = CashRegister::with('user')
            ->whereBetween('opening_date', [$this->from, $this->to])
            ->orderBy('opening_date')
            ->get();

        // Todos los montos se envían como string: PhpSpreadsheet trata un
        // número igual a 0 como equivalente a null (comparación floja) y
        // deja la celda vacía — crítico aquí porque una diferencia de caja
        // de 0 (cuadre perfecto) es el resultado más importante de leer bien.
        $rows = [
            ['Libro Diario / Caja Diaria'],
            ['Período', $this->from . ' a ' . $this->to],
            [''],
            ['Total ventas (activas)', (string) $sales->sum('total')],
            ['Total compras (activas)', (string) $purchases->sum('total')],
            ['Total ingresos de caja', (string) $income],
            ['Total egresos de caja', (string) $expense],
            [''],
            ['Cajas del período'],
            ['Usuario', 'Fecha apertura', 'Monto inicial', 'Monto esperado', 'Monto contado', 'Diferencia', 'Estado'],
        ];

        foreach ($registers as $register) {
            $rows[] = [
                $register->user?->name,
                optional($register->opening_date)->format('d/m/Y'),
                (string) $register->opening_amount,
                (string) $register->expected_amount,
                $register->closing_amount !== null ? (string) $register->closing_amount : null,
                $register->difference !== null ? (string) $register->difference : null,
                $register->status === 'OPEN' ? 'Abierta' : 'Cerrada',
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Resumen';
    }
}
