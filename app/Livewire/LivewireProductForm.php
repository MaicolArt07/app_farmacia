<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use Livewire\Component;
use App\Models\Laboratory;
use App\Models\Presentation;
use App\Livewire\Form\ProductForm;

class LivewireProductForm extends Component
{
    public ProductForm $form;

    public function mount($product = null)
    {
        $this->form->resetForm();

        if ($product) {
            $product = Product::findOrFail($product);
            $this->form->fillForm($product);
        }
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            $data = [
                'id_category' => $this->form->id_category,
                'id_laboratory' => $this->form->id_laboratory ?: null,
                'id_presentation' => $this->form->id_presentation,
                'id_brand' => $this->form->id_brand ?: null,
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
                Product::create($data);
                $message = 'Producto registrado correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);

            return redirect()->route('products');
        } catch (\Throwable $e) {
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
            'brands' => Brand::where('state', 1)->orderBy('name')->get(),
        ]);
    }
}
