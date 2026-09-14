<?php

namespace App\Livewire;

use App\Models\Lot;
use Livewire\Component;
use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryAdjustment;

class LivewireInventoryAdjustmentForm extends Component
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

        $this->productResults = Product::where('state', 1)
            ->where(function ($q) {
                $q->where('code', 'like', "%{$this->productSearch}%")
                  ->orWhere('name', 'like', "%{$this->productSearch}%")
                  ->orWhere('generic_name', 'like', "%{$this->productSearch}%");
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
        $product = Product::findOrFail($id);

        $this->id_product = $product->id;
        $this->selectedProductLabel = $product->code . ' - ' . $product->name;

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

        $this->lotResults = Lot::where('id_product', $this->id_product)
            ->where('state', 1)
            ->where(function ($q) {
                $q->where('batch_code', 'like', "%{$this->lotSearch}%")
                  ->orWhere('expiration_date', 'like', "%{$this->lotSearch}%");
            })
            ->limit(10)
            ->get()
            ->map(fn ($lot) => [
                'id' => $lot->id,
                'label' => $lot->batch_code . ' | Vence: ' . (optional($lot->expiration_date)->format('d/m/Y') ?? 'Sin vencimiento') . ' | Stock: ' . $lot->quantity_available,
            ])
            ->toArray();
    }

    public function selectLot($id)
    {
        $lot = Lot::findOrFail($id);

        $this->id_lot = $lot->id;
        $this->selectedLotLabel = $lot->batch_code . ' | Vence: ' . (optional($lot->expiration_date)->format('d/m/Y') ?? 'Sin vencimiento') . ' | Stock: ' . $lot->quantity_available;

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
            'id_product' => 'required|exists:products,id',
            'id_lot' => 'required|exists:lots,id',
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
            $lot = Lot::where('id', $this->id_lot)
                ->where('id_product', $this->id_product)
                ->lockForUpdate()
                ->firstOrFail();

            $stockBefore = $lot->quantity_available;

            if ($this->type === 'DECREASE' && $this->quantity > $lot->quantity_available) {
                DB::rollBack();
                $this->dispatch('toast', type: 'warning', message: 'No puede disminuir más stock del disponible.');
                return;
            }

            if ($this->type === 'INCREASE') {
                $lot->quantity_available += (int) $this->quantity;
                $lot->quantity_in += (int) $this->quantity;
                $movementType = 'ADJUSTMENT';
                $description = 'Ajuste positivo de inventario.';
            } else {
                $lot->quantity_available -= (int) $this->quantity;
                $movementType = 'ADJUSTMENT';
                $description = 'Ajuste negativo de inventario.';
            }

            $lot->save();

            $adjustment = InventoryAdjustment::create([
                'id_product' => $this->id_product,
                'id_lot' => $this->id_lot,
                'id_user' => auth()->id(),
                'type' => $this->type,
                'quantity' => $this->quantity,
                'reason' => $this->reason,
                'stock_before' => $stockBefore,
                'stock_after' => $lot->quantity_available,
                'state' => 1,
            ]);

            InventoryMovement::create([
                'id_product' => $this->id_product,
                'id_lot' => $this->id_lot,
                'id_user' => auth()->id(),
                'movement_type' => $movementType,
                'reference_type' => 'INVENTORY_ADJUSTMENT',
                'reference_id' => $adjustment->id,
                'quantity' => (int) $this->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $lot->quantity_available,
                'description' => $description . ' Motivo: ' . $this->reason,
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
