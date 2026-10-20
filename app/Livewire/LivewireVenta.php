<?php

namespace App\Livewire;

use App\Models\Venta;
use App\Models\Lote;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\MetodoPago;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use App\Models\DetalleVenta;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\Facturacion\FacturacionServiceInterface;
use App\Livewire\Form\VentaForm;

class LivewireVenta extends Component
{
    use WithPagination;

    public $search = '';

    public $mode = 'list';
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

    protected $paginationTheme = 'tailwind';

    public function mount($sale = null)
    {
        $this->refreshCashStatus();

        if (request()->routeIs('ventas.create') || request()->routeIs('ventas.view')) {
            $this->mode = 'form';
            $this->viewMode = request()->routeIs('ventas.view');

            $this->form->resetForm();
            $this->details = [];

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
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        if ($this->mode === 'form') {
            return view('livewire.venta.form', [
                'paymentMethods' => MetodoPago::where('estado', 1)->orderBy('nombre')->get(),
            ]);
        }

        $query = Venta::with(['client.person', 'user', 'invoice']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('fecha_venta', 'like', "%{$this->search}%")
                    ->orWhere('metodo_pago', 'like', "%{$this->search}%")
                    ->orWhereHas('client.person', function ($sub) {
                        $sub->where('nombre', 'like', "%{$this->search}%")
                            ->orWhere('apellido', 'like', "%{$this->search}%")
                            ->orWhere('ci', 'like', "%{$this->search}%");
                    });
            });
        }

        return view('livewire.venta.index', [
            'sales' => $query->orderBy('id', 'desc')->paginate(10),
            'openCash' => $this->openCash,
            'cashExpired' => $this->cashExpired,
        ]);
    }

    public function refreshCashStatus()
    {
        $this->openCash = Caja::where('id_user', Auth::id())
            ->where('status', 'OPEN')
            ->where('estado', 1)
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

    public function generarFactura($id)
    {
        try {
            $sale = Venta::with(['client.person', 'invoice'])->findOrFail($id);

            if ($sale->status === 'CANCELLED') {
                $this->dispatch('toast', type: 'warning', message: 'No se puede facturar una venta anulada.');
                return;
            }

            if ($sale->invoice && in_array($sale->invoice->estado, ['ENVIADA'], true)) {
                $this->dispatch('toast', type: 'warning', message: 'Esta venta ya tiene una factura generada.');
                return;
            }

            app(FacturacionServiceInterface::class)->generar($sale);

            $this->dispatch('toast', type: 'success', message: 'Factura generada correctamente.');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo generar la factura. ' . $e->getMessage());
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

    public function updatedFormDescuento()
    {
        $this->calculateTotals();
    }

    public function updatedFormMontoPagado()
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

        $discount = (float) ($this->form->descuento ?? 0);

        if ($discount > $subtotal) {
            $discount = $subtotal;
            $this->form->descuento = $discount;
        }

        $this->form->subtotal = $subtotal;
        $this->form->total = $subtotal - $discount;

        $amountPaid = (float) ($this->form->monto_pagado ?? 0);
        $this->form->monto_cambio = $amountPaid > $this->form->total ? $amountPaid - $this->form->total : 0;
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

        $amountPaid = $this->form->monto_pagado === '' ? null : (float) $this->form->monto_pagado;

        $paymentMethod = MetodoPago::findOrFail($this->form->id_payment_method);

        if (strtoupper($paymentMethod->nombre) === 'EFECTIVO') {
            if ($amountPaid === null || $amountPaid < $this->form->total) {
                $this->dispatch('toast', type: 'warning', message: 'El monto recibido debe ser mayor o igual al total de la venta.');
                return;
            }
        }

        DB::beginTransaction();

        try {
            $this->calculateTotals();

            $cash = Caja::where('id', $cash->id)
                ->where('status', 'OPEN')
                ->lockForUpdate()
                ->firstOrFail();

            $sale = Venta::create([
                'id_client' => $this->form->id_client ?: null,
                'id_user' => Auth::id(),
                'id_cash_register' => $cash->id,
                'fecha_venta' => $this->form->fecha_venta,
                'id_payment_method' => $paymentMethod->id,
                'metodo_pago' => $paymentMethod->nombre,
                'subtotal' => $this->form->subtotal,
                'descuento' => $this->form->descuento ?: 0,
                'total' => $this->form->total,
                'monto_pagado' => $amountPaid,
                'monto_cambio' => $amountPaid !== null ? round($amountPaid - $this->form->total, 2) : null,
                'observacion' => $this->form->observacion ?: null,
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

                MovimientoInventario::create([
                    'id_product' => $detail['id_product'],
                    'id_lot' => $lot->id,
                    'id_user' => Auth::id(),
                    'tipo_movimiento' => 'OUT',
                    'reference_type' => 'SALE',
                    'reference_id' => $sale->id,
                    'quantity' => (int) $detail['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $lot->cantidad_disponible,
                    'descripcion' => 'Salida por venta #' . $sale->id,
                ]);
            }

            MovimientoCaja::create([
                'id_cash_register' => $cash->id,
                'id_user' => Auth::id(),
                'type' => 'INCOME',
                'concepto' => 'Venta #' . $sale->id,
                'monto' => $sale->total,
                'reference_type' => 'SALE',
                'reference_id' => $sale->id,
                'observacion' => 'Ingreso automático por venta.',
            ]);

            $cash->monto_esperado += $sale->total;
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

    public function cancelSale($id)
    {
        DB::beginTransaction();

        try {
            $sale = Venta::with(['details.lot', 'invoice'])
                ->where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($sale->status === 'CANCELLED') {
                DB::rollBack();
                $this->dispatch('toast', type: 'warning', message: 'La venta ya está anulada.');
                return;
            }

            if ($sale->invoice && $sale->invoice->estado === 'ENVIADA') {
                DB::rollBack();
                $this->dispatch(
                    'toast',
                    type: 'warning',
                    message: 'Esta venta tiene una factura generada. Anule la factura desde el módulo de Facturación antes de anular la venta.'
                );
                return;
            }

            foreach ($sale->details as $detail) {
                $lot = Lote::where('id', $detail->id_lot)
                    ->lockForUpdate()
                    ->firstOrFail();

                $stockBefore = $lot->cantidad_disponible;

                $lot->cantidad_disponible += $detail->quantity;
                $lot->save();

                MovimientoInventario::create([
                    'id_product' => $detail->id_product,
                    'id_lot' => $lot->id,
                    'id_user' => Auth::id(),
                    'tipo_movimiento' => 'RETURN',
                    'reference_type' => 'SALE_CANCEL',
                    'reference_id' => $sale->id,
                    'quantity' => $detail->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $lot->cantidad_disponible,
                    'descripcion' => 'Devolución de stock por anulación de venta #' . $sale->id,
                ]);
            }

            $cash = Caja::where('id_user', Auth::id())
                ->where('status', 'OPEN')
                ->where('estado', 1)
                ->lockForUpdate()
                ->first();

            if ($cash) {
                MovimientoCaja::create([
                    'id_cash_register' => $cash->id,
                    'id_user' => Auth::id(),
                    'type' => 'EXPENSE',
                    'concepto' => 'Anulación venta #' . $sale->id,
                    'monto' => $sale->total,
                    'observacion' => 'Reversión automática por anulación de venta.',
                ]);

                $cash->monto_esperado -= $sale->total;
                $cash->save();
            }

            $sale->status = 'CANCELLED';
            $sale->estado = 0;
            $sale->cancelled_at = now();
            $sale->cancelled_by = Auth::id();
            $sale->cancel_reason = 'Venta anulada desde el módulo de ventas.';
            $sale->save();

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Venta anulada, stock restaurado y caja ajustada.');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo anular la venta. ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $sale = Venta::findOrFail($id);
            $sale->estado = $sale->estado ? 0 : 1;
            $sale->save();

            $this->dispatch(
                'toast',
                type: $sale->estado ? 'success' : 'warning',
                message: $sale->estado
                    ? 'Venta activada correctamente.'
                    : 'Venta desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado de la venta.');
        }
    }
}
