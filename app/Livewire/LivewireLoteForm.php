<?php

namespace App\Livewire;

use App\Models\Lot;
use App\Models\Product;
use Livewire\Component;
use App\Livewire\Form\LotForm;

class LivewireLotForm extends Component
{
    public LotForm $form;

    public $productSearch = '';
    public $productResults = [];
    public $selectedProductLabel = '';

    public function mount($lot = null)
    {
        $this->form->resetForm();

        if ($lot) {
            $lot = Lot::with('product')->findOrFail($lot);
            $this->form->fillForm($lot);

            if ($lot->product) {
                $this->selectedProductLabel = $lot->product->code . ' - ' . $lot->product->name .
                    ($lot->product->concentration ? ' | ' . $lot->product->concentration : '');
            }
        }
    }

    public function updatedProductSearch()
    {
        if (strlen(trim($this->productSearch)) < 2) {
            $this->productResults = [];
            return;
        }

        $this->productResults = Product::query()
            ->where('state', 1)
            ->where(function ($q) {
                $q->where('code', 'like', "%{$this->productSearch}%")
                  ->orWhere('barcode', 'like', "%{$this->productSearch}%")
                  ->orWhere('name', 'like', "%{$this->productSearch}%")
                  ->orWhere('generic_name', 'like', "%{$this->productSearch}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'label' => $product->code . ' - ' . $product->name .
                        ($product->concentration ? ' | ' . $product->concentration : ''),
                ];
            })
            ->toArray();
    }

    public function selectProduct($id)
    {
        $product = Product::findOrFail($id);

        $this->form->id_product = $product->id;
        $this->selectedProductLabel = $product->code . ' - ' . $product->name .
            ($product->concentration ? ' | ' . $product->concentration : '');

        if (!$this->form->sale_price || $this->form->sale_price == 0) {
            $this->form->sale_price = $product->sale_price;
        }

        $this->productSearch = '';
        $this->productResults = [];
    }

    public function clearProduct()
    {
        $this->form->id_product = null;
        $this->selectedProductLabel = '';
        $this->productSearch = '';
        $this->productResults = [];
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        if ($this->form->quantity_available > $this->form->quantity_in) {
            $this->dispatch('toast', type: 'warning', message: 'La cantidad disponible no puede ser mayor a la cantidad ingresada.');
            return;
        }

        try {
            $expirationDate = $this->form->expiration_date ?: null;

            $data = [
                'id_product' => $this->form->id_product,
                'batch_code' => $this->form->batch_code,
                'purchase_price' => $this->form->purchase_price,
                'sale_price' => $this->form->sale_price,
                'quantity_in' => $this->form->quantity_in,
                'quantity_available' => $this->form->quantity_available,
                'expiration_date' => $expirationDate,
                'location' => $this->form->location ?: null,
                'state' => $this->form->state,
            ];

            if ($this->form->id) {
                $lot = Lot::findOrFail($this->form->id);
                $lot->update($data);
                $message = 'Lote actualizado correctamente.';
            } else {
                $exists = Lot::where('id_product', $this->form->id_product)
                    ->where('batch_code', $this->form->batch_code)
                    ->when($expirationDate, fn ($q) => $q->where('expiration_date', $expirationDate))
                    ->when(!$expirationDate, fn ($q) => $q->whereNull('expiration_date'))
                    ->exists();

                if ($exists) {
                    $this->dispatch('toast', type: 'warning', message: 'Ya existe un lote con ese producto, código y vencimiento.');
                    return;
                }

                Lot::create($data);
                $message = 'Lote registrado correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);

            return redirect()->route('lots');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el lote.');
        }
    }

    public function cancel()
    {
        return redirect()->route('lots');
    }

    public function render()
    {
        return view('livewire.lot.form');
    }
}
