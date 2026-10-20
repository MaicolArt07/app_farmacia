<?php

namespace App\Livewire;

use App\Models\Lote;
use App\Models\Producto;
use Livewire\Component;
use App\Livewire\Form\LoteForm;

class LivewireLoteForm extends Component
{
    public LoteForm $form;

    public $productSearch = '';
    public $productResults = [];
    public $selectedProductLabel = '';

    public function mount($lot = null)
    {
        $this->form->resetForm();

        if ($lot) {
            $lot = Lote::with('product')->findOrFail($lot);
            $this->form->fillForm($lot);

            if ($lot->product) {
                $this->selectedProductLabel = $lot->product->code . ' - ' . $lot->product->nombre .
                    ($lot->product->concentracion ? ' | ' . $lot->product->concentracion : '');
            }
        }
    }

    public function updatedProductSearch()
    {
        if (strlen(trim($this->productSearch)) < 2) {
            $this->productResults = [];
            return;
        }

        $this->productResults = Producto::query()
            ->where('estado', 1)
            ->where(function ($q) {
                $q->where('code', 'like', "%{$this->productSearch}%")
                  ->orWhere('barcode', 'like', "%{$this->productSearch}%")
                  ->orWhere('nombre', 'like', "%{$this->productSearch}%")
                  ->orWhere('nombre_generico', 'like', "%{$this->productSearch}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'label' => $product->code . ' - ' . $product->nombre .
                        ($product->concentracion ? ' | ' . $product->concentracion : ''),
                ];
            })
            ->toArray();
    }

    public function selectProduct($id)
    {
        $product = Producto::findOrFail($id);

        $this->form->id_product = $product->id;
        $this->selectedProductLabel = $product->code . ' - ' . $product->nombre .
            ($product->concentracion ? ' | ' . $product->concentracion : '');

        if (!$this->form->precio_venta || $this->form->precio_venta == 0) {
            $this->form->precio_venta = $product->precio_venta;
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

        if ($this->form->cantidad_disponible > $this->form->cantidad_ingresada) {
            $this->dispatch('toast', type: 'warning', message: 'La cantidad disponible no puede ser mayor a la cantidad ingresada.');
            return;
        }

        try {
            $expirationDate = $this->form->fecha_vencimiento ?: null;

            $data = [
                'id_product' => $this->form->id_product,
                'codigo_lote' => $this->form->codigo_lote,
                'precio_compra' => $this->form->precio_compra,
                'precio_venta' => $this->form->precio_venta,
                'cantidad_ingresada' => $this->form->cantidad_ingresada,
                'cantidad_disponible' => $this->form->cantidad_disponible,
                'fecha_vencimiento' => $expirationDate,
                'ubicacion' => $this->form->ubicacion ?: null,
                'estado' => $this->form->estado,
            ];

            if ($this->form->id) {
                $lot = Lote::findOrFail($this->form->id);
                $lot->update($data);
                $message = 'Lote actualizado correctamente.';
            } else {
                $exists = Lote::where('id_product', $this->form->id_product)
                    ->where('codigo_lote', $this->form->codigo_lote)
                    ->when($expirationDate, fn ($q) => $q->where('fecha_vencimiento', $expirationDate))
                    ->when(!$expirationDate, fn ($q) => $q->whereNull('fecha_vencimiento'))
                    ->exists();

                if ($exists) {
                    $this->dispatch('toast', type: 'warning', message: 'Ya existe un lote con ese producto, código y vencimiento.');
                    return;
                }

                Lote::create($data);
                $message = 'Lote registrado correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);

            return redirect()->route('lotes');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el lote.');
        }
    }

    public function cancel()
    {
        return redirect()->route('lotes');
    }

    public function render()
    {
        return view('livewire.lote.form');
    }
}
