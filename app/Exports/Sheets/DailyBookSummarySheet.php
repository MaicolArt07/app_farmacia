<?php

namespace App\Exports\Sheets;

use App\Models\MovimientoCaja;
use App\Models\Caja;
use App\Models\Compra;
use App\Models\Venta;
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
        $sales = Venta::where('status', 'ACTIVE')
            ->whereBetween('fecha_venta', [$this->from . ' 00:00:00', $this->to . ' 23:59:59'])
            ->get();

        $purchases = Compra::where('status', 'ACTIVE')
            ->whereBetween('fecha_compra', [$this->from, $this->to])
            ->get();

        $movements = MovimientoCaja::whereHas('cashRegister', function ($q) {
            $q->whereBetween('fecha_apertura', [$this->from, $this->to]);
        })->get();

        $income = $movements->where('type', 'INCOME')->sum('monto');
        $expense = $movements->where('type', 'EXPENSE')->sum('monto');

        $registers = Caja::with('user')
            ->whereBetween('fecha_apertura', [$this->from, $this->to])
            ->orderBy('fecha_apertura')
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
                optional($register->fecha_apertura)->format('d/m/Y'),
                (string) $register->monto_apertura,
                (string) $register->monto_esperado,
                $register->monto_cierre !== null ? (string) $register->monto_cierre : null,
                $register->diferencia !== null ? (string) $register->diferencia : null,
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
