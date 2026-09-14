<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return Product::with(['category', 'brand', 'lots' => function ($q) {
            $q->where('state', 1);
        }])
            ->where('state', 1)
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return ['Código', 'Producto', 'Categoría', 'Marca', 'Stock actual', 'Stock mínimo', 'Precio de venta', 'Costo promedio', 'Utilidad estimada'];
    }

    public function map($product): array
    {
        $activeLots = $product->lots->where('quantity_available', '>', 0);

        $stock = $activeLots->sum('quantity_available');
        $avgCost = $activeLots->isNotEmpty() ? (float) $activeLots->avg('purchase_price') : 0.0;
        $utilidad = (float) $product->sale_price - $avgCost;

        // string: evita que PhpSpreadsheet trate un valor igual a 0 (muy
        // frecuente en stock mínimo, stock disponible y costo/utilidad)
        // como null y deje la celda vacía.
        return [
            $product->code,
            $product->name,
            $product->category?->name,
            $product->brand?->name,
            (string) $stock,
            (string) $product->minimum_stock,
            (string) $product->sale_price,
            (string) round($avgCost, 2),
            (string) round($utilidad, 2),
        ];
    }
}
