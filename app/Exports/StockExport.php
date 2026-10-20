<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return Producto::with(['category', 'laboratory', 'lots' => function ($q) {
            $q->where('estado', 1);
        }])
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();
    }

    public function headings(): array
    {
        return ['Código', 'Producto', 'Categoría', 'Laboratorio', 'Stock actual', 'Stock mínimo', 'Precio de venta', 'Costo promedio', 'Utilidad estimada'];
    }

    public function map($product): array
    {
        $activeLots = $product->lots->where('cantidad_disponible', '>', 0);

        $stock = $activeLots->sum('cantidad_disponible');
        $avgCost = $activeLots->isNotEmpty() ? (float) $activeLots->avg('precio_compra') : 0.0;
        $utilidad = (float) $product->precio_venta - $avgCost;

        // string: evita que PhpSpreadsheet trate un valor igual a 0 (muy
        // frecuente en stock mínimo, stock disponible y costo/utilidad)
        // como null y deje la celda vacía.
        return [
            $product->code,
            $product->nombre,
            $product->category?->nombre,
            $product->laboratory?->nombre,
            (string) $stock,
            (string) $product->stock_minimo,
            (string) $product->precio_venta,
            (string) round($avgCost, 2),
            (string) round($utilidad, 2),
        ];
    }
}
