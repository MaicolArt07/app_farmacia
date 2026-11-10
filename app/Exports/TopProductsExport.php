<?php

namespace App\Exports;

use App\Support\TopProductsReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class TopProductsExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    private int $headerRow = 4;
    private int $lastRow = 4;

    public function __construct(private string $from, private string $to, private string $order = 'cantidad')
    {
    }

    public function title(): string
    {
        return 'Más vendidos';
    }

    public function array(): array
    {
        $rows = TopProductsReport::rows($this->from, $this->to, $this->order);
        $t = TopProductsReport::totals($rows);
        $fmt = fn ($d) => \Carbon\Carbon::parse($d)->format('d/m/Y');

        // Montos como string: PhpSpreadsheet deja vacía una celda con 0 numérico.
        $data = [
            ['PRODUCTOS MÁS VENDIDOS'],
            ['Del ' . $fmt($this->from) . ' al ' . $fmt($this->to) . ' — ordenado por ' . mb_strtolower(TopProductsReport::ORDERS[$this->order] ?? 'cantidad')],
            [],
            ['N°', 'Código', 'Producto', 'Laboratorio', 'Cantidad vendida', 'N° de ventas', 'Precio prom. (Bs)', 'Total vendido (Bs)', 'Costo (Bs)', 'Ganancia (Bs)'],
        ];

        foreach ($rows as $r) {
            $data[] = [
                (string) $r->puesto, $r->code, $r->nombre, $r->laboratorio ?? '-',
                (string) $r->cantidad, (string) $r->num_ventas,
                number_format($r->precio_promedio, 2, '.', ''),
                number_format($r->total_vendido, 2, '.', ''),
                number_format($r->costo, 2, '.', ''),
                number_format($r->ganancia, 2, '.', ''),
            ];
        }

        $data[] = [
            '', '', 'TOTAL (' . $t['productos'] . ' productos)', '', (string) $t['cantidad'], '', '',
            number_format($t['total_vendido'], 2, '.', ''),
            number_format($t['costo'], 2, '.', ''),
            number_format($t['ganancia'], 2, '.', ''),
        ];

        $this->lastRow = count($data);

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $s = $event->sheet->getDelegate();
                $s->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $s->getStyle("A{$this->headerRow}:J{$this->headerRow}")->getFont()->setBold(true);
                $s->getStyle("A{$this->headerRow}:J{$this->headerRow}")->getFill()
                    ->setFillType('solid')->getStartColor()->setRGB('E4E4E7');
                $s->getStyle("A{$this->lastRow}:J{$this->lastRow}")->getFont()->setBold(true);
                $s->getStyle("E{$this->headerRow}:J{$this->lastRow}")->getAlignment()->setHorizontal('right');
                $s->freezePane('A' . ($this->headerRow + 1));
            },
        ];
    }
}
