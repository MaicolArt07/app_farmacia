<?php

namespace App\Exports;

use App\Models\InventoryMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KardexExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private string $from, private string $to, private ?int $productId = null)
    {
    }

    public function collection()
    {
        return InventoryMovement::with(['product', 'lot', 'user'])
            ->whereBetween('created_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59'])
            ->when($this->productId, fn ($q) => $q->where('id_product', $this->productId))
            ->orderBy('created_at')
            ->get();
    }

    public function headings(): array
    {
        return ['Fecha', 'Producto', 'Lote', 'Tipo', 'Referencia', 'Cantidad', 'Stock antes', 'Stock después', 'Usuario', 'Descripción'];
    }

    public function map($movement): array
    {
        // string: evita que PhpSpreadsheet trate una cantidad/stock igual a
        // 0 como null y deje la celda vacía.
        return [
            optional($movement->created_at)->format('d/m/Y H:i'),
            $movement->product?->name,
            $movement->lot?->batch_code ?? '-',
            $movement->movement_type,
            $movement->reference_type . ($movement->reference_id ? ' #' . $movement->reference_id : ''),
            (string) $movement->quantity,
            (string) $movement->stock_before,
            (string) $movement->stock_after,
            $movement->user?->name,
            $movement->description,
        ];
    }
}
