<?php

namespace App\Livewire;

use App\Models\Lote;
use Livewire\Component;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use App\Models\AjusteInventario;

class LivewireAjusteInventarioForm extends Component
{
    public $productSearch = '';
    public $productResults = [];
    public $selectedProductLabel = '';

    public $lotSearch = '';
    public $lotResults = [];
    public $selectedLotLabel = '';

    public $id_product = null;
    public $id_lot = null;
    public $type = 'INCREASE';
    public $quantity = '';
    public $reason = '';

    public function updatedProductSearch()
    {
        if (strlen(trim($this->productSearch)) < 2) {
            $this->productResults = [];
            return;
        }

        $this->productResults = Producto::where('estado', 1)
            ->where(function ($q) {
                $q->where('code', 'like', "%{$this->productSearch}%")
                  ->orWhere('nombre', 'like', "%{$this->productSearch}%")
                  ->orWhere('nombre_generico', 'like', "%{$this->productSearch}%");
            })
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'label' => $item->code . ' - ' . $item->name,
            ])
            ->toArray();
    }

    public function selectProduct($id)
    {
        $product = Producto::findOrFail($id);

        $this->id_product = $product->id;
        $this->selectedProductLabel = $product->code . ' - ' . $product->nombre;

        $this->productSearch = '';
        $this->productResults = [];

        $this->clearLot();
    }

    public function resetProduct()
    {
        $this->id_product = null;
        $this->selectedProductLabel = '';
        $this->productSearch = '';
        $this->productResults = [];
        $this->clearLot();
    }

    public function updatedLotSearch()
    {
        if (!$this->id_product || strlen(trim($this->lotSearch)) < 1) {
            $this->lotResults = [];
            return;
        }

        $this->lotResults = Lote::where('id_product', $this->id_product)
            ->where('estado', 1)
            ->where(function ($q) {
                $q->where('codigo_lote', 'like', "%{$this->lotSearch}%")
                  ->orWhere('fecha_vencimiento', 'like', "%{$this->lotSearch}%");
            })
            ->limit(10)
            ->get()
            ->map(fn ($lot) => [
                'id' => $lot->id,
                'label' => $lot->codigo_lote . ' | Vence: ' . (optional($lot->fecha_vencimiento)->format('d/m/Y') ?? 'Sin vencimiento') . ' | Stock: ' . $lot->cantidad_disponible,
            ])
            ->toArray();
    }

    public function selectLot($id)
    {
        $lot = Lote::findOrFail($id);

        $this->id_lot = $lot->id;
        $this->selectedLotLabel = $lot->codigo_lote . ' | Vence: ' . (optional($lot->fecha_vencimiento)->format('d/m/Y') ?? 'Sin vencimiento') . ' | Stock: ' . $lot->cantidad_disponible;

        $this->lotSearch = '';
        $this->lotResults = [];
    }

    public function clearLot()
    {
        $this->id_lot = null;
        $this->selectedLotLabel = '';
        $this->lotSearch = '';
        $this->lotResults = [];
    }

    public function save()
    {
        $this->validate([
            'id_product' => 'required|exists:productos,id',
            'id_lot' => 'required|exists:lotes,id',
            'type' => 'required|in:INCREASE,DECREASE',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|min:5',
        ], [
            'id_product.required' => 'Debe seleccionar un producto.',
            'id_lot.required' => 'Debe seleccionar un lote.',
            'quantity.required' => 'Debe ingresar la cantidad.',
            'reason.required' => 'Debe ingresar el motivo.',
        ]);

        DB::beginTransaction();

        try {
            $lot = Lote::where('id', $this->id_lot)
                ->where('id_product', $this->id_product)
                ->lockForUpdate()
                ->firstOrFail();

            $stockBefore = $lot->cantidad_disponible;

            if ($this->type === 'DECREASE' && $this->quantity > $lot->cantidad_disponible) {
                DB::rollBack();
                $this->dispatch('toast', type: 'warning', message: 'No puede disminuir más stock del disponible.');
                return;
            }

            if ($this->type === 'INCREASE') {
                $lot->cantidad_disponible += (int) $this->quantity;
                $lot->cantidad_ingresada += (int) $this->quantity;
                $movementType = 'ADJUSTMENT';
                $description = 'Ajuste positivo de inventario.';
            } else {
                $lot->cantidad_disponible -= (int) $this->quantity;
                $movementType = 'ADJUSTMENT';
                $description = 'Ajuste negativo de inventario.';
            }

            $lot->save();

            $adjustment = AjusteInventario::create([
                'id_product' => $this->id_product,
                'id_lot' => $this->id_lot,
                'id_user' => auth()->id(),
                'type' => $this->type,
                'quantity' => $this->quantity,
                'motivo' => $this->reason,
                'stock_before' => $stockBefore,
                'stock_after' => $lot->cantidad_disponible,
                'estado' => 1,
            ]);

            MovimientoInventario::create([
                'id_product' => $this->id_product,
                'id_lot' => $this->id_lot,
                'id_user' => auth()->id(),
                'tipo_movimiento' => $movementType,
                'reference_type' => 'INVENTORY_ADJUSTMENT',
                'reference_id' => $adjustment->id,
                'quantity' => (int) $this->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $lot->cantidad_disponible,
                'descripcion' => $description . ' Motivo: ' . $this->reason,
            ]);

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Ajuste registrado correctamente.');

            return redirect()->route('inventory-adjustments');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo registrar el ajuste.');
        }
    }

    public function cancel()
    {
        return redirect()->route('inventory-adjustments');
    }

    public function render()
    {
        return view('livewire.inventory-adjustment.form');
    }
}
