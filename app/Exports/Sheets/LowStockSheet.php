<?php

namespace App\Exports\Sheets;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LowStockSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function collection()
    {
        return Producto::with('lots')
            ->where('estado', 1)
            ->get()
            ->filter(function ($product) {
                $stock = $product->lots->where('estado', 1)->sum('cantidad_disponible');
                return $stock <= $product->stock_minimo;
            })
            ->values();
    }

    public function headings(): array
    {
        return ['Código', 'Producto', 'Stock actual', 'Stock mínimo'];
    }

    public function map($product): array
    {
        $stock = $product->lots->where('estado', 1)->sum('cantidad_disponible');

        // string: evita que PhpSpreadsheet trate un stock igual a 0 (el
        // caso más importante de esta hoja) como null y deje la celda vacía.
        return [
            $product->code,
            $product->nombre,
            (string) $stock,
            (string) $product->stock_minimo,
        ];
    }

    public function title(): string
    {
        return 'Stock Bajo';
    }
}
