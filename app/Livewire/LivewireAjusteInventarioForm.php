<?php

namespace App\Livewire;

use App\Models\Lote;
use Livewire\Component;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use App\Models\AjusteInventario;
use App\Livewire\Form\AjusteInventarioForm;

class LivewireAjusteInventarioForm extends Component
{
    public AjusteInventarioForm $form;

    public $productSearch = '';
    public $productResults = [];
    public $selectedProductLabel = '';

    public $lotSearch = '';
    public $lotResults = [];
    public $selectedLotLabel = '';

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
                'label' => $item->code . ' - ' . $item->nombre,
            ])
            ->toArray();
    }

    public function selectProduct($id)
    {
        $product = Producto::findOrFail($id);

        $this->form->id_product = $product->id;
        $this->selectedProductLabel = $product->code . ' - ' . $product->nombre;

        $this->productSearch = '';
        $this->productResults = [];

        $this->clearLot();
    }

    public function resetProduct()
    {
        $this->form->id_product = null;
        $this->selectedProductLabel = '';
        $this->productSearch = '';
        $this->productResults = [];
        $this->clearLot();
    }

    public function updatedLotSearch()
    {
        if (!$this->form->id_product || strlen(trim($this->lotSearch)) < 1) {
            $this->lotResults = [];
            return;
        }

        $this->lotResults = Lote::where('id_product', $this->form->id_product)
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

        $this->form->id_lot = $lot->id;
        $this->selectedLotLabel = $lot->codigo_lote . ' | Vence: ' . (optional($lot->fecha_vencimiento)->format('d/m/Y') ?? 'Sin vencimiento') . ' | Stock: ' . $lot->cantidad_disponible;

        $this->lotSearch = '';
        $this->lotResults = [];
    }

    public function clearLot()
    {
        $this->form->id_lot = null;
        $this->selectedLotLabel = '';
        $this->lotSearch = '';
        $this->lotResults = [];
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        DB::beginTransaction();

        try {
            $lot = Lote::where('id', $this->form->id_lot)
                ->where('id_product', $this->form->id_product)
                ->lockForUpdate()
                ->firstOrFail();

            $stockBefore = $lot->cantidad_disponible;

            if ($this->form->type === 'DECREASE' && $this->form->quantity > $lot->cantidad_disponible) {
                DB::rollBack();
                $this->dispatch('toast', type: 'warning', message: 'No puede disminuir más stock del disponible.');
                return;
            }

            if ($this->form->type === 'INCREASE') {
                $lot->cantidad_disponible += (int) $this->form->quantity;
                $lot->cantidad_ingresada += (int) $this->form->quantity;
                $movementType = 'ADJUSTMENT';
                $description = 'Ajuste positivo de inventario.';
            } else {
                $lot->cantidad_disponible -= (int) $this->form->quantity;
                $movementType = 'ADJUSTMENT';
                $description = 'Ajuste negativo de inventario.';
            }

            $lot->save();

            $adjustment = AjusteInventario::create([
                'id_product' => $this->form->id_product,
                'id_lot' => $this->form->id_lot,
                'id_user' => auth()->id(),
                'type' => $this->form->type,
                'quantity' => $this->form->quantity,
                'motivo' => $this->form->reason,
                'stock_before' => $stockBefore,
                'stock_after' => $lot->cantidad_disponible,
                'estado' => 1,
            ]);

            MovimientoInventario::create([
                'id_product' => $this->form->id_product,
                'id_lot' => $this->form->id_lot,
                'id_user' => auth()->id(),
                'tipo_movimiento' => $movementType,
                'reference_type' => 'INVENTORY_ADJUSTMENT',
                'reference_id' => $adjustment->id,
                'quantity' => (int) $this->form->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $lot->cantidad_disponible,
                'descripcion' => $description . ' Motivo: ' . $this->form->reason,
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
