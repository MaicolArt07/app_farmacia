<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Category;
use App\Models\Lot;
use App\Models\InventoryMovement;
use Livewire\Component;
use App\Models\Laboratory;
use App\Models\Presentation;
use App\Livewire\Form\ProductForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LivewireProductForm extends Component
{
    public ProductForm $form;

    public $isCreating = true;
    public $step = 1;

    public $registerInitialStock = true;
    public $initial_batch_code = '';
    public $initial_purchase_price = '';
    public $initial_quantity = '';
    public $initial_expiration_date = '';

    public function mount($product = null)
    {
        $this->form->resetForm();
        $this->resetStockFields();

        if ($product) {
            $product = Product::findOrFail($product);
            $this->form->fillForm($product);
            $this->isCreating = false;
        }
    }

    protected function resetStockFields()
    {
        $this->registerInitialStock = true;
        $this->initial_batch_code = '';
        $this->initial_purchase_price = '';
        $this->initial_quantity = '';
        $this->initial_expiration_date = '';
    }

    public function nextStep()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        $this->initial_batch_code = 'INICIAL-' . $this->form->code;
        $this->step = 2;
    }

    public function prevStep()
    {
        $this->step = 1;
    }

    public function skipStock()
    {
        $this->registerInitialStock = false;
        $this->save();
    }

    protected function stockRules()
    {
        return [
            'initial_batch_code' => 'required|string|max:100',
            'initial_purchase_price' => 'required|numeric|min:0',
            'initial_quantity' => 'required|integer|min:1',
            'initial_expiration_date' => 'nullable|date',
        ];
    }

    protected function stockMessages()
    {
        return [
            'initial_batch_code.required' => 'El código de lote es obligatorio.',
            'initial_purchase_price.required' => 'El precio de compra es obligatorio.',
            'initial_purchase_price.numeric' => 'El precio de compra debe ser numérico.',
            'initial_quantity.required' => 'La cantidad inicial es obligatoria.',
            'initial_quantity.integer' => 'La cantidad inicial debe ser un número entero.',
            'initial_quantity.min' => 'La cantidad inicial debe ser mayor a cero.',
        ];
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        if ($this->isCreating && $this->registerInitialStock) {
            $this->validate($this->stockRules(), $this->stockMessages());
        }

        DB::beginTransaction();

        try {
            $data = [
                'id_category' => $this->form->id_category,
                'id_laboratory' => $this->form->id_laboratory ?: null,
                'id_presentation' => $this->form->id_presentation,
                'code' => $this->form->code,
                'barcode' => $this->form->barcode ?: null,
                'name' => $this->form->name,
                'generic_name' => $this->form->generic_name ?: null,
                'concentration' => $this->form->concentration ?: null,
                'description' => $this->form->description ?: null,
                'sale_price' => $this->form->sale_price,
                'minimum_stock' => $this->form->minimum_stock,
                'requires_prescription' => $this->form->requires_prescription ? 1 : 0,
                'state' => $this->form->state,
            ];

            if ($this->form->id) {
                $product = Product::findOrFail($this->form->id);
                $product->update($data);
                $message = 'Producto actualizado correctamente.';
            } else {
                $product = Product::create($data);
                $message = 'Producto registrado correctamente.';

                if ($this->registerInitialStock) {
                    $lot = Lot::create([
                        'id_product' => $product->id,
                        'batch_code' => $this->initial_batch_code,
                        'purchase_price' => $this->initial_purchase_price,
                        'sale_price' => $this->form->sale_price,
                        'quantity_in' => $this->initial_quantity,
                        'quantity_available' => $this->initial_quantity,
                        'expiration_date' => $this->initial_expiration_date ?: null,
                        'state' => 1,
                    ]);

                    InventoryMovement::create([
                        'id_product' => $product->id,
                        'id_lot' => $lot->id,
                        'id_user' => Auth::id(),
                        'movement_type' => 'IN',
                        'reference_type' => 'INITIAL_STOCK',
                        'reference_id' => null,
                        'quantity' => $this->initial_quantity,
                        'stock_before' => 0,
                        'stock_after' => $this->initial_quantity,
                        'description' => 'Stock inicial registrado junto con el producto.',
                    ]);

                    $message = 'Producto y stock inicial registrados correctamente.';
                }
            }

            DB::commit();

            $this->dispatch('toast', type: 'success', message: $message);

            return redirect()->route('products');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el producto.');
        }
    }

    public function cancel()
    {
        return redirect()->route('products');
    }

    public function render()
    {
        return view('livewire.product.form', [
            'categories' => Category::where('state', 1)->orderBy('name')->get(),
            'laboratories' => Laboratory::where('state', 1)->orderBy('name')->get(),
            'presentations' => Presentation::where('state', 1)->orderBy('name')->get(),
        ]);
    }
}
