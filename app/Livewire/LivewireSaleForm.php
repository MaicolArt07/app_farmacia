<?php

namespace App\Livewire;

use App\Models\Lot;
use App\Models\Sale;
use App\Models\Client;
use App\Models\Product;
use App\Models\CashRegister;
use App\Models\CashMovement;
use App\Models\InventoryMovement;
use Livewire\Component;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Form\SaleForm;

class LivewireSaleForm extends Component
{
    public SaleForm $form;

    public $viewMode = false;

    public $clientSearch = '';
    public $clientResults = [];
    public $selectedClientLabel = '';

    public $productSearch = '';
    public $productResults = [];

    public $details = [];

    public function mount($sale = null)
    {
        $this->form->resetForm();
        $this->details = [];
        $this->viewMode = request()->routeIs('sales.view');

        if ($sale) {
            $sale = Sale::with(['client.person', 'details.product', 'details.lot'])->findOrFail($sale);
            $this->form->fillForm($sale);

            if ($sale->client) {
                $this->selectedClientLabel = trim(($sale->client->person?->name ?? '') . ' ' . ($sale->client->person?->lastname ?? '')) .
                    ' - CI: ' . ($sale->client->person?->ci ?? '-') .
                    ' - Cod: ' . $sale->client->code;
            }

            foreach ($sale->details as $detail) {
                $this->details[] = [
                    'id_product' => $detail->id_product,
                    'id_lot' => $detail->id_lot,
                    'product_label' => $detail->product?->code . ' - ' . $detail->product?->name,
                    'lot_label' => $detail->lot?->batch_code . ' | Vence: ' . optional($detail->lot?->expiration_date)->format('d/m/Y'),
                    'quantity' => $detail->quantity,
                    'available' => $detail->lot?->quantity_available ?? 0,
                    'sale_price' => (float) $detail->sale_price,
                    'subtotal' => (float) $detail->subtotal,
                ];
            }
        }
    }

    public function updatedClientSearch()
    {
        if (strlen(trim($this->clientSearch)) < 2) {
            $this->clientResults = [];
            return;
        }

        $this->clientResults = Client::with('person')
            ->where('state', 1)
            ->where(function ($q) {
                $q->where('code', 'like', "%{$this->clientSearch}%")
                    ->orWhere('nit', 'like', "%{$this->clientSearch}%")
                    ->orWhere('email', 'like', "%{$this->clientSearch}%")
                    ->orWhereHas('person', function ($sub) {
                        $sub->where('name', 'like', "%{$this->clientSearch}%")
                            ->orWhere('lastname', 'like', "%{$this->clientSearch}%")
                            ->orWhere('ci', 'like', "%{$this->clientSearch}%");
                    });
            })
            ->limit(8)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'label' => trim(($client->person?->name ?? '') . ' ' . ($client->person?->lastname ?? '')) .
                        ' - CI: ' . ($client->person?->ci ?? '-') .
                        ' - Cod: ' . $client->code,
                ];
            })
            ->toArray();
    }

    public function selectClient($id)
    {
        $client = Client::with('person')->findOrFail($id);

        $this->form->id_client = $client->id;
        $this->selectedClientLabel = trim(($client->person?->name ?? '') . ' ' . ($client->person?->lastname ?? '')) .
            ' - CI: ' . ($client->person?->ci ?? '-') .
            ' - Cod: ' . $client->code;

        $this->clientSearch = '';
        $this->clientResults = [];
    }

    public function clearClient()
    {
        $this->form->id_client = null;
        $this->selectedClientLabel = '';
        $this->clientSearch = '';
        $this->clientResults = [];
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
                $stock = Lot::where('id_product', $product->id)
                    ->where('state', 1)
                    ->where('quantity_available', '>', 0)
                    ->where(function ($q) {
                        $q->whereNull('expiration_date')
                            ->orWhereDate('expiration_date', '>=', now()->toDateString());
                    })
                    ->sum('quantity_available');

                return [
                    'id' => $product->id,
                    'label' => $product->code . ' - ' . $product->name .
                        ($product->concentration ? ' | ' . $product->concentration : '') .
                        ' | Stock: ' . $stock,
                    'sale_price' => (float) $product->sale_price,
                    'stock' => $stock,
                ];
            })
            ->toArray();
    }

    public function addProductToDetail($id)
    {
        $product = Product::findOrFail($id);

        $availableLot = Lot::where('id_product', $product->id)
            ->where('state', 1)
            ->where('quantity_available', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expiration_date')
                    ->orWhereDate('expiration_date', '>=', now()->toDateString());
            })
            ->orderByRaw('expiration_date IS NULL')
            ->orderBy('expiration_date', 'asc')
            ->first();

        if (!$availableLot) {
            $this->dispatch('toast', type: 'warning', message: 'El producto no tiene lote disponible o no vencido.');
            return;
        }

        $lotExpirationLabel = optional($availableLot->expiration_date)->format('d/m/Y') ?? 'Sin vencimiento';

        $this->details[] = [
            'id_product' => $product->id,
            'id_lot' => $availableLot->id,
            'product_label' => $product->code . ' - ' . $product->name . ($product->concentration ? ' | ' . $product->concentration : ''),
            'lot_label' => $availableLot->batch_code . ' | Vence: ' . $lotExpirationLabel . ' | Stock: ' . $availableLot->quantity_available,
            'quantity' => 1,
            'available' => $availableLot->quantity_available,
            'sale_price' => (float) $availableLot->sale_price,
            'subtotal' => (float) $availableLot->sale_price,
        ];

        $this->productSearch = '';
        $this->productResults = [];

        $this->calculateTotals();
    }

    public function removeDetail($index)
    {
        unset($this->details[$index]);
        $this->details = array_values($this->details);
        $this->calculateTotals();
    }

    public function updatedDetails()
    {
        $this->calculateTotals();
    }

    public function updatedFormDiscount()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $subtotal = 0;

        foreach ($this->details as $i => $detail) {
            $qty = (int) ($detail['quantity'] ?? 0);
            $price = (float) ($detail['sale_price'] ?? 0);

            $lineSubtotal = $qty * $price;
            $this->details[$i]['subtotal'] = $lineSubtotal;

            $subtotal += $lineSubtotal;
        }

        $discount = (float) ($this->form->discount ?? 0);

        if ($discount > $subtotal) {
            $discount = $subtotal;
            $this->form->discount = $discount;
        }

        $this->form->subtotal = $subtotal;
        $this->form->total = $subtotal - $discount;
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        if (count($this->details) === 0) {
            $this->dispatch('toast', type: 'warning', message: 'Debe agregar al menos un producto a la venta.');
            return;
        }

        $cash = CashRegister::where('id_user', Auth::id())
            ->where('status', 'OPEN')
            ->where('state', 1)
            ->first();

        if (!$cash) {
            $this->dispatch('toast', type: 'warning', message: 'Debe abrir caja antes de registrar ventas.');
            return;
        }

        foreach ($this->details as $detail) {
            if (
                empty($detail['id_product']) ||
                empty($detail['id_lot']) ||
                (int) $detail['quantity'] <= 0 ||
                (float) $detail['sale_price'] < 0
            ) {
                $this->dispatch('toast', type: 'warning', message: 'Revise el detalle de venta. Hay filas incompletas.');
                return;
            }
        }

        DB::beginTransaction();

        try {
            $this->calculateTotals();

            $cash = CashRegister::where('id', $cash->id)
                ->where('status', 'OPEN')
                ->lockForUpdate()
                ->firstOrFail();

            $sale = Sale::create([
                'id_client' => $this->form->id_client ?: null,
                'id_user' => Auth::id(),
                'sale_date' => $this->form->sale_date,
                'payment_method' => $this->form->payment_method,
                'subtotal' => $this->form->subtotal,
                'discount' => $this->form->discount ?: 0,
                'total' => $this->form->total,
                'observation' => $this->form->observation ?: null,
                'state' => 1,
                'status' => 'ACTIVE',
            ]);

            foreach ($this->details as $detail) {
                $lot = Lot::where('id', $detail['id_lot'])
                    ->where('state', 1)
                    ->where('quantity_available', '>=', (int) $detail['quantity'])
                    ->where(function ($q) {
                        $q->whereNull('expiration_date')
                            ->orWhereDate('expiration_date', '>=', now()->toDateString());
                    })
                    ->lockForUpdate()
                    ->first();

                if (!$lot) {
                    throw new \Exception('Stock insuficiente o lote vencido.');
                }

                $stockBefore = $lot->quantity_available;

                SaleDetail::create([
                    'id_sale' => $sale->id,
                    'id_product' => $detail['id_product'],
                    'id_lot' => $detail['id_lot'],
                    'quantity' => $detail['quantity'],
                    'sale_price' => $detail['sale_price'],
                    'subtotal' => $detail['subtotal'],
                ]);

                $lot->quantity_available -= (int) $detail['quantity'];
                $lot->save();

                InventoryMovement::create([
                    'id_product' => $detail['id_product'],
                    'id_lot' => $lot->id,
                    'id_user' => Auth::id(),
                    'movement_type' => 'OUT',
                    'reference_type' => 'SALE',
                    'reference_id' => $sale->id,
                    'quantity' => (int) $detail['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $lot->quantity_available,
                    'description' => 'Salida por venta #' . $sale->id,
                ]);
            }

            CashMovement::create([
                'id_cash_register' => $cash->id,
                'id_user' => Auth::id(),
                'type' => 'INCOME',
                'concept' => 'Venta #' . $sale->id,
                'amount' => $sale->total,
                'observation' => 'Ingreso automático por venta.',
            ]);

            $cash->expected_amount += $sale->total;
            $cash->save();

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Venta registrada correctamente.');

            return redirect()->route('sales');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar la venta. ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('sales');
    }

    public function render()
    {
        return view('livewire.sale.form');
    }
}
