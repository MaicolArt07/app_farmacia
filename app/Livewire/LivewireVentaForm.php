<?php

namespace App\Livewire;

use App\Models\Lote;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\CashRegister;
use App\Models\CashMovement;
use App\Models\PaymentMethod;
use App\Models\InventoryMovement;
use Livewire\Component;
use App\Models\DetalleVenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Form\VentaForm;

class LivewireVentaForm extends Component
{
    public VentaForm $form;

    public $viewMode = false;

    public $clientSearch = '';
    public $clientResults = [];
    public $selectedClientLabel = '';

    public $productSearch = '';
    public $productResults = [];

    public $details = [];

    public $openCash = null;
    public $cashExpired = false;

    public function mount($sale = null)
    {
        $this->form->resetForm();
        $this->details = [];
        $this->viewMode = request()->routeIs('ventas.view');
        $this->refreshCashStatus();

        if ($sale) {
            $sale = Venta::with(['client.person', 'details.product', 'details.lot'])->findOrFail($sale);
            $this->form->fillForm($sale);

            if ($sale->client) {
                $this->selectedClientLabel = trim(($sale->client->person?->nombre ?? '') . ' ' . ($sale->client->person?->apellido ?? '')) .
                    ' - CI: ' . ($sale->client->person?->ci ?? '-') .
                    ' - Cod: ' . $sale->client->code;
            }

            foreach ($sale->details as $detail) {
                $this->details[] = [
                    'id_product' => $detail->id_product,
                    'id_lot' => $detail->id_lot,
                    'product_label' => $detail->product?->code . ' - ' . $detail->product?->nombre,
                    'lot_label' => $detail->lot?->codigo_lote . ' | Vence: ' . optional($detail->lot?->fecha_vencimiento)->format('d/m/Y'),
                    'quantity' => $detail->quantity,
                    'available' => $detail->lot?->cantidad_disponible ?? 0,
                    'sale_price' => (float) $detail->precio_venta,
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

        $this->clientResults = Cliente::with('person')
            ->where('estado', 1)
            ->where(function ($q) {
                $q->where('code', 'like', "%{$this->clientSearch}%")
                    ->orWhere('nit', 'like', "%{$this->clientSearch}%")
                    ->orWhere('email', 'like', "%{$this->clientSearch}%")
                    ->orWhereHas('person', function ($sub) {
                        $sub->where('nombre', 'like', "%{$this->clientSearch}%")
                            ->orWhere('apellido', 'like', "%{$this->clientSearch}%")
                            ->orWhere('ci', 'like', "%{$this->clientSearch}%");
                    });
            })
            ->limit(8)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'label' => trim(($client->person?->nombre ?? '') . ' ' . ($client->person?->apellido ?? '')) .
                        ' - CI: ' . ($client->person?->ci ?? '-') .
                        ' - Cod: ' . $client->code,
                ];
            })
            ->toArray();
    }

    public function selectClient($id)
    {
        $client = Cliente::with('person')->findOrFail($id);

        $this->form->id_client = $client->id;
        $this->selectedClientLabel = trim(($client->person?->nombre ?? '') . ' ' . ($client->person?->apellido ?? '')) .
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

        $this->productResults = Producto::query()
            ->with(['laboratory', 'presentation'])
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
                $stock = Lote::where('id_product', $product->id)
                    ->where('estado', 1)
                    ->where('cantidad_disponible', '>', 0)
                    ->where(function ($q) {
                        $q->whereNull('fecha_vencimiento')
                            ->orWhereDate('fecha_vencimiento', '>=', now()->toDateString());
                    })
                    ->sum('cantidad_disponible');

                return [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->nombre . ($product->concentracion ? ' | ' . $product->concentracion : ''),
                    'laboratory' => $product->laboratory?->nombre,
                    'presentation' => $product->presentation?->nombre,
                    'label' => $product->code . ' - ' . $product->nombre .
                        ($product->concentracion ? ' | ' . $product->concentracion : '') .
                        ' | Stock: ' . $stock,
                    'sale_price' => (float) $product->precio_venta,
                    'stock' => $stock,
                ];
            })
            ->toArray();
    }

    public function addProductToDetail($id)
    {
        $product = Producto::findOrFail($id);

        $availableLot = Lote::where('id_product', $product->id)
            ->where('estado', 1)
            ->where('cantidad_disponible', '>', 0)
            ->where(function ($q) {
                $q->whereNull('fecha_vencimiento')
                    ->orWhereDate('fecha_vencimiento', '>=', now()->toDateString());
            })
            ->orderByRaw('fecha_vencimiento IS NULL')
            ->orderBy('fecha_vencimiento', 'asc')
            ->first();

        if (!$availableLot) {
            $this->dispatch('toast', type: 'warning', message: 'El producto no tiene lote disponible o no vencido.');
            return;
        }

        $lotExpirationLabel = optional($availableLot->fecha_vencimiento)->format('d/m/Y') ?? 'Sin vencimiento';

        $this->details[] = [
            'id_product' => $product->id,
            'id_lot' => $availableLot->id,
            'product_label' => $product->code . ' - ' . $product->nombre . ($product->concentracion ? ' | ' . $product->concentracion : ''),
            'lot_label' => $availableLot->codigo_lote . ' | Vence: ' . $lotExpirationLabel . ' | Stock: ' . $availableLot->cantidad_disponible,
            'quantity' => 1,
            'available' => $availableLot->cantidad_disponible,
            'sale_price' => (float) $availableLot->precio_venta,
            'subtotal' => (float) $availableLot->precio_venta,
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
        foreach ($this->details as $i => $detail) {
            $available = (int) ($detail['available'] ?? 0);
            $quantity = (int) ($detail['quantity'] ?? 0);

            if ($quantity < 1) {
                $this->details[$i]['quantity'] = 1;
            } elseif ($quantity > $available) {
                $this->details[$i]['quantity'] = $available;
            }
        }

        $this->calculateTotals();
    }

    public function updatedFormDiscount()
    {
        $this->calculateTotals();
    }

    public function updatedFormAmountPaid()
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

        $amountPaid = (float) ($this->form->amount_paid ?? 0);
        $this->form->change_amount = $amountPaid > $this->form->total ? $amountPaid - $this->form->total : 0;
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        if (count($this->details) === 0) {
            $this->dispatch('toast', type: 'warning', message: 'Debe agregar al menos un producto a la venta.');
            return;
        }

        $this->refreshCashStatus();
        $cash = $this->openCash;

        if (!$cash) {
            $this->dispatch('toast', type: 'warning', message: 'Debe abrir caja antes de registrar ventas.');
            return;
        }

        if ($cash->isExpired()) {
            $this->dispatch('toast', type: 'warning', message: 'Su caja está vencida. Ciérrela o amplíe su vigencia antes de continuar.');
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

        $this->calculateTotals();

        $amountPaid = $this->form->amount_paid === '' ? null : (float) $this->form->amount_paid;

        $paymentMethod = PaymentMethod::findOrFail($this->form->id_payment_method);

        if (strtoupper($paymentMethod->name) === 'EFECTIVO') {
            if ($amountPaid === null || $amountPaid < $this->form->total) {
                $this->dispatch('toast', type: 'warning', message: 'El monto recibido debe ser mayor o igual al total de la venta.');
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

            $sale = Venta::create([
                'id_client' => $this->form->id_client ?: null,
                'id_user' => Auth::id(),
                'id_cash_register' => $cash->id,
                'fecha_venta' => $this->form->sale_date,
                'id_payment_method' => $paymentMethod->id,
                'metodo_pago' => $paymentMethod->name,
                'subtotal' => $this->form->subtotal,
                'descuento' => $this->form->discount ?: 0,
                'total' => $this->form->total,
                'monto_pagado' => $amountPaid,
                'monto_cambio' => $amountPaid !== null ? round($amountPaid - $this->form->total, 2) : null,
                'observacion' => $this->form->observation ?: null,
                'estado' => 1,
                'status' => 'ACTIVE',
            ]);

            foreach ($this->details as $detail) {
                $lot = Lote::where('id', $detail['id_lot'])
                    ->where('estado', 1)
                    ->where('cantidad_disponible', '>=', (int) $detail['quantity'])
                    ->where(function ($q) {
                        $q->whereNull('fecha_vencimiento')
                            ->orWhereDate('fecha_vencimiento', '>=', now()->toDateString());
                    })
                    ->lockForUpdate()
                    ->first();

                if (!$lot) {
                    throw new \Exception('Stock insuficiente o lote vencido.');
                }

                $stockBefore = $lot->cantidad_disponible;

                DetalleVenta::create([
                    'id_sale' => $sale->id,
                    'id_product' => $detail['id_product'],
                    'id_lot' => $detail['id_lot'],
                    'quantity' => $detail['quantity'],
                    'precio_venta' => $detail['sale_price'],
                    'subtotal' => $detail['subtotal'],
                ]);

                $lot->cantidad_disponible -= (int) $detail['quantity'];
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
                    'stock_after' => $lot->cantidad_disponible,
                    'description' => 'Salida por venta #' . $sale->id,
                ]);
            }

            CashMovement::create([
                'id_cash_register' => $cash->id,
                'id_user' => Auth::id(),
                'type' => 'INCOME',
                'concept' => 'Venta #' . $sale->id,
                'amount' => $sale->total,
                'reference_type' => 'SALE',
                'reference_id' => $sale->id,
                'observation' => 'Ingreso automático por venta.',
            ]);

            $cash->expected_amount += $sale->total;
            $cash->save();

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Venta registrada correctamente.');

            return redirect()->route('ventas');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar la venta. ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('ventas');
    }

    public function refreshCashStatus()
    {
        $this->openCash = CashRegister::where('id_user', Auth::id())
            ->where('status', 'OPEN')
            ->where('state', 1)
            ->first();

        $this->cashExpired = $this->openCash ? $this->openCash->isExpired() : false;
    }

    public function extendCash()
    {
        if (!$this->openCash) {
            return;
        }

        $this->openCash->extend();
        $this->refreshCashStatus();

        $this->dispatch('toast', type: 'success', message: 'Vigencia de la caja ampliada correctamente.');
    }

    public function render()
    {
        return view('livewire.venta.form', [
            'paymentMethods' => PaymentMethod::where('state', 1)->orderBy('name')->get(),
        ]);
    }
}
