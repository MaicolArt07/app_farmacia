<?php

namespace App\Livewire;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Lote;
use App\Models\InventoryMovement;
use Livewire\Component;
use App\Models\Laboratorio;
use App\Models\Presentacion;
use App\Models\SinProductCode;
use App\Livewire\Form\ProductoForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LivewireProductoForm extends Component
{
    public ProductoForm $form;

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
            $product = Producto::findOrFail($product);
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
                'id_sin_product_code' => $this->form->id_sin_product_code ?: null,
                'code' => $this->form->code,
                'barcode' => $this->form->barcode ?: null,
                'nombre' => $this->form->nombre,
                'nombre_generico' => $this->form->nombre_generico ?: null,
                'concentracion' => $this->form->concentracion ?: null,
                'descripcion' => $this->form->descripcion ?: null,
                'precio_venta' => $this->form->precio_venta,
                'stock_minimo' => $this->form->stock_minimo,
                'requiere_receta' => $this->form->requiere_receta ? 1 : 0,
                'estado' => $this->form->estado,
            ];

            if ($this->form->id) {
                $product = Producto::findOrFail($this->form->id);
                $product->update($data);
                $message = 'Producto actualizado correctamente.';
            } else {
                $product = Producto::create($data);
                $message = 'Producto registrado correctamente.';

                if ($this->registerInitialStock) {
                    $lot = Lote::create([
                        'id_product' => $product->id,
                        'codigo_lote' => $this->initial_batch_code,
                        'precio_compra' => $this->initial_purchase_price,
                        'precio_venta' => $this->form->precio_venta,
                        'cantidad_ingresada' => $this->initial_quantity,
                        'cantidad_disponible' => $this->initial_quantity,
                        'fecha_vencimiento' => $this->initial_expiration_date ?: null,
                        'estado' => 1,
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

            return redirect()->route('productos');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el producto.');
        }
    }

    public function cancel()
    {
        return redirect()->route('productos');
    }

    public function render()
    {
        return view('livewire.producto.form', [
            'categories' => Categoria::where('estado', 1)->orderBy('nombre')->get(),
            'laboratories' => Laboratorio::where('estado', 1)->orderBy('nombre')->get(),
            'presentations' => Presentacion::where('estado', 1)->orderBy('nombre')->get(),
            'sinProductCodes' => SinProductCode::orderBy('descripcion')->get(),
        ]);
    }
}
