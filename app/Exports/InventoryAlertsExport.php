<?php

namespace App\Exports;

use App\Support\InventoryAlerts;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Alertas de inventario en Excel (mismas listas que la pantalla de
 * Notificaciones): Stock bajo, Vencidos y Por vencer, con laboratorio.
 */
class InventoryAlertsExport implements WithMultipleSheets
{
    public function __construct(private string $term = '')
    {
    }

    public function sheets(): array
    {
        $l = InventoryAlerts::lists($this->term);
        $lab = fn ($p) => $p?->laboratory?->sufijo ?: 'Sin laboratorio';
        $fecha = fn ($d) => $d ? $d->format('d/m/Y') : '';

        // Valores como string: PhpSpreadsheet deja vacía una celda con 0 numérico.
        $low = $l['low']->map(fn ($p) => [
            $p->code, $p->nombre_completo, $lab($p), (string) $p->stock_actual, (string) $p->stock_minimo,
            $p->stock_actual <= 0 ? 'Agotado' : 'Stock bajo',
        ])->all();

        $expired = $l['expired']->map(fn ($lot) => [
            $lot->product?->code, $lot->product?->nombre_completo, $lab($lot->product), $lot->codigo_lote,
            $fecha($lot->fecha_vencimiento), (string) $lot->days, (string) $lot->cantidad_disponible,
        ])->all();

        $near = $l['near']->map(fn ($lot) => [
            $lot->product?->code, $lot->product?->nombre_completo, $lab($lot->product), $lot->codigo_lote,
            $fecha($lot->fecha_vencimiento), (string) $lot->days, (string) $lot->cantidad_disponible,
        ])->all();

        return [
            $this->sheet('Stock bajo', ['Código', 'Producto', 'Laboratorio', 'Stock actual', 'Stock mínimo', 'Estado'], $low),
            $this->sheet('Vencidos', ['Código', 'Producto', 'Laboratorio', 'Lote', 'Vencimiento', 'Días vencido', 'Stock'], $expired),
            $this->sheet('Por vencer (30 días)', ['Código', 'Producto', 'Laboratorio', 'Lote', 'Vencimiento', 'Días que faltan', 'Stock'], $near),
        ];
    }

    private function sheet(string $title, array $headings, array $rows)
    {
        return new class($title, $headings, $rows) implements FromArray, WithTitle, ShouldAutoSize, WithEvents {
            public function __construct(private string $title, private array $headings, private array $rows)
            {
            }

            public function array(): array
            {
                return array_merge([$this->headings], $this->rows ?: [['Sin registros']]);
            }

            public function title(): string
            {
                return $this->title;
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $s = $event->sheet->getDelegate();
                        $last = $s->getHighestColumn();
                        $s->getStyle("A1:{$last}1")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
                        $s->getStyle("A1:{$last}1")->getFill()->setFillType('solid')->getStartColor()->setRGB('3F3F46');
                        $s->setAutoFilter("A1:{$last}" . max(1, $s->getHighestRow()));
                        $s->freezePane('A2');
                    },
                ];
            }
        };
    }
}
