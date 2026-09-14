<?php

namespace App\Exports\Sheets;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LowStockSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function collection()
    {
        return Product::with('lots')
            ->where('state', 1)
            ->get()
            ->filter(function ($product) {
                $stock = $product->lots->where('state', 1)->sum('quantity_available');
                return $stock <= $product->minimum_stock;
            })
            ->values();
    }

    public function headings(): array
    {
        return ['Código', 'Producto', 'Stock actual', 'Stock mínimo'];
    }

    public function map($product): array
    {
        $stock = $product->lots->where('state', 1)->sum('quantity_available');

        // string: evita que PhpSpreadsheet trate un stock igual a 0 (el
        // caso más importante de esta hoja) como null y deje la celda vacía.
        return [
            $product->code,
            $product->name,
            (string) $stock,
            (string) $product->minimum_stock,
        ];
    }

    public function title(): string
    {
        return 'Stock Bajo';
    }
}
