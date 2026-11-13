<?php

namespace App\Livewire;

use App\Models\Venta;
use App\Models\Lote;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
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
use App\Support\ScopedToSucursal;

class LivewireVenta extends Component
{
    use WithPagination, ScopedToSucursal;

    public $search = '';

    /** Filtro del listado por método de pago: '', EFECTIVO, QR o MIXTO. */
    public $paymentFilter = '';

    /** Filtro de fechas del listado (solo administrador). */
    public $dateFrom = '';
    public $dateTo = '';

    public $mode = 'list';
    public VentaForm $form;

    public $viewMode = false;

    /** Editar solo el pago (método/monto) de una venta con caja aún abierta. */
    public $editMode = false;

    public $clientSearch = '';
    public $clientResults = [];
    public $selectedClientLabel = '';

    public $productSearch = '';
    public $productResults = [];

    public $serviceSearch = '';
    public $serviceResults = [];

    public $details = [];

    public $openCash = null;
    public $cashExpired = false;

    // Solo en modo "ver venta": fecha/hora, estado y motivo de cancelación.
    public $saleInfo = null;

    protected $paginationTheme = 'tailwind';

    public function mount($sale = null)
    {
        $this->refreshCashStatus();

        if (request()->routeIs('ventas.create') || request()->routeIs('ventas.view') || request()->routeIs('ventas.edit')) {
            $this->mode = 'form';
            $this->viewMode = request()->routeIs('ventas.view') || request()->routeIs('ventas.edit');
            $this->editMode = request()->routeIs('ventas.edit');

            $this->form->resetForm();
            $this->details = [];

            if ($sale) {
                $sale = Venta::with(['client.person', 'details.product', 'details.lot', 'details.service', 'cashRegister'])->findOrFail($sale);

                if ($this->editMode) {
                    if (!Auth::user()?->hasRole('admin')) {
                        abort(403);
                    }

                    if ($sale->status !== 'ACTIVE' || $sale->cashRegister?->status !== 'OPEN') {
                        $this->dispatch('toast', type: 'warning', message: 'Esta venta ya no se puede editar: su caja está cerrada.');
                        $this->redirectRoute('ventas.view', $sale->id, navigate: true);
                        return;
                    }
                }

                $this->form->fillForm($sale);

                $this->saleInfo = [
                    'fecha_hora' => $sale->created_at?->format('d/m/Y H:i'),
                    'cancelled' => $sale->status === 'CANCELLED',
                    'cancel_reason' => $sale->cancel_reason,
                    'cancelled_at' => $sale->cancelled_at?->format('d/m/Y H:i'),
                ];

                if ($sale->client) {
                    $this->selectedClientLabel = trim(($sale->client->person?->nombre ?? '') . ' ' . ($sale->client->person?->apellido ?? '')) .
                        ' - CI: ' . ($sale->client->person?->ci ?? '-') .
                        ' - Cod: ' . $sale->client->code;
                }

                foreach ($sale->details as $detail) {
                    if ($detail->id_servicio) {
                        $this->details[] = [
                            'type' => 'servicio',
                            'id_servicio' => $detail->id_servicio,
                            'product_label' => $detail->service?->nombre,
                            'lot_label' => 'Servicio',
                            'quantity' => (float) $detail->cantidad_servicio,
                            'available' => null,
                            'sale_price' => (float) $detail->precio_venta,
                            'subtotal' => (float) $detail->subtotal,
                        ];
                        continue;
                    }

                    $this->details[] = [
                        'type' => 'producto',
                        'id_producto' => $detail->id_producto,
                        'id_lote' => $detail->id_lote,
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

    public function setPaymentFilter($value)
    {
        $this->paymentFilter = in_array($value, ['EFECTIVO', 'QR', 'MIXTO'], true) ? $value : '';
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function clearDateFilter()
    {
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function render()
    {
        if ($this->mode === 'form') {
            return view('livewire.venta.form', [
                'paymentMethods' => MetodoPago::where('estado', 1)->orderBy('nombre')->get(),
            ]);
        }

        $isAdmin = (bool) Auth::user()?->hasRole('admin');

        if ($isAdmin) {
            // El administrador ve el historial completo (todas las cajas,
            // abiertas o cerradas, cualquier fecha) y puede filtrar por rango.
            $query = $this->scopeToUserSucursal(Venta::with(['client.person', 'user', 'invoice', 'details.product', 'details.service', 'cashRegister']));

            if ($this->dateFrom !== '') {
                $query->whereDate('fecha_venta', '>=', $this->dateFrom);
            }
            if ($this->dateTo !== '') {
                $query->whereDate('fecha_venta', '<=', $this->dateTo);
            }
        } else {
            // Sin historial: solo se listan las ventas de SU caja ABIERTA y del
            // DÍA de hoy. Al cerrar la caja o terminar el día dejan de aparecer
            // (siguen disponibles en el detalle de la caja).
            $openCashIds = Caja::where('status', 'OPEN')
                ->where('estado', 1)
                ->where('id_usuario', Auth::id())
                ->pluck('id');

            $query = Venta::with(['client.person', 'user', 'invoice', 'details.product', 'details.service', 'cashRegister'])
                ->whereIn('id_caja', $openCashIds)
                ->whereDate('created_at', now()->toDateString());
        }

        $term = trim((string) $this->search);
        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('fecha_venta', 'like', "%{$term}%")
                    ->orWhere('metodo_pago', 'like', "%{$term}%")
                    ->when(ctype_digit(ltrim($term, '#')), fn ($q) => $q->orWhere('id', (int) ltrim($term, '#')))
                    // Por PRODUCTO (nombre, código o barras) o servicio vendido.
                    ->orWhereHas('details.product', fn ($p) => $p->where('nombre', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%")
                        ->orWhere('barcode', 'like', "%{$term}%"))
                    ->orWhereHas('details.service', fn ($s) => $s->where('nombre', 'like', "%{$term}%"))
                    ->orWhereHas('client.person', function ($sub) use ($term) {
                        $sub->where('nombre', 'like', "%{$term}%")
                            ->orWhere('apellido', 'like', "%{$term}%")
                            ->orWhere('ci', 'like', "%{$term}%");
                    });
            });
        }

        if ($this->paymentFilter !== '') {
            $query->whereRaw('UPPER(metodo_pago) = ?', [$this->paymentFilter]);
        }

        // Totales de lo vendido (sin contar ventas canceladas).
        $sold = (clone $query)->where('status', 'ACTIVE');
        $totals = [
            // Mixto reparte: su efectivo suma a Efectivo y su QR a QR.
            'efectivo' => (float) (clone $sold)->sum(DB::raw(Venta::SQL_CASH)),
            'qr' => (float) (clone $sold)->sum(DB::raw(Venta::SQL_QR)),
            'total' => (float) (clone $sold)->sum('total'),
            'cantidad' => (clone $sold)->count(),
        ];

        return view('livewire.venta.index', [
            'totals' => $totals,
            'sales' => $query->orderBy('id', 'desc')->paginate(10),
            'openCash' => $this->openCash,
            'cashExpired' => $this->cashExpired,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function refreshCashStatus()
    {
        $this->openCash = Caja::where('id_usuario', Auth::id())
            ->where('status', 'OPEN')
            ->where('estado', 1)
            ->first();

        $this->cashExpired = $this->openCash ? $this->openCash->isExpired() : false;
    }

    public function extendCash()
    {
        if (!Auth::user()?->can('Editar Caja')) {
            $this->dispatch('toast', type: 'error', message: 'No tiene permiso para ampliar la vigencia de la caja.');
            return;
        }

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

        $this->form->id_cliente = $client->id;
        $this->selectedClientLabel = trim(($client->person?->nombre ?? '') . ' ' . ($client->person?->apellido ?? '')) .
            ' - CI: ' . ($client->person?->ci ?? '-') .
            ' - Cod: ' . $client->code;

        $this->clientSearch = '';
        $this->clientResults = [];
    }

    public function clearClient()
    {
        $this->form->id_cliente = null;
        $this->selectedClientLabel = '';
        $this->clientSearch = '';
        $this->clientResults = [];
    }

    public function updatedProductSearch()
    {
        $term = trim(preg_replace('/\s+/', ' ', (string) $this->productSearch));

        if (mb_strlen($term) < 2) {
            $this->productResults = [];
            return;
        }

        // Cada palabra debe aparecer (en cualquier orden) en el nombre, genérico,
        // laboratorio, código o barras. Ej: "refrianex noche", "amoxi sae".
        $words = array_slice(explode(' ', $term), 0, 5);

        $this->productResults = Producto::query()
            ->with(['laboratory', 'presentation'])
            ->where('estado', 1)
            ->where(function ($q) use ($term) {
                // Código/barras exacto o parcial (lector de código de barras).
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('barcode', 'like', "%{$term}%")
                    ->orWhere(function ($q) {
                        foreach (array_slice(explode(' ', trim(preg_replace('/\s+/', ' ', (string) $this->productSearch))), 0, 5) as $w) {
                            // Una letra suelta (ej. la "D" de "DIA D") solo como palabra completa.
                            if (mb_strlen($w) === 1) {
                                $q->where(fn ($q) => $q->where('nombre', 'like', "% {$w}")
                                    ->orWhere('nombre', 'like', "% {$w} %")
                                    ->orWhere('nombre', 'like', "{$w} %"));
                                continue;
                            }
                            $q->where(fn ($q) => $q->where('nombre', 'like', "%{$w}%")
                                ->orWhere('nombre_generico', 'like', "%{$w}%")
                                ->orWhereHas('laboratory', fn ($l) => $l->where('nombre', 'like', "%{$w}%")));
                        }
                    });
            })
            // Primero los que EMPIEZAN con lo escrito, luego por nombre.
            ->orderByRaw('CASE WHEN nombre LIKE ? THEN 0 ELSE 1 END', [$words[0] . '%'])
            ->orderBy('nombre')
            ->limit(15)
            ->get()
            ->map(function ($product) {
                // Cada lote (ingreso) es un stock con su propio precio.
                $lots = $this->scopeToUserSucursal(Lote::where('id_producto', $product->id)
                    ->where('estado', 1)
                    ->where('cantidad_disponible', '>', 0)
                    ->where(function ($q) {
                        $q->whereNull('fecha_vencimiento')
                            ->orWhereDate('fecha_vencimiento', '>=', now()->toDateString());
                    }))
                    ->orderBy('id') // primero el stock que entró antes
                    ->get()
                    ->map(fn ($l) => [
                        'id' => $l->id,
                        'price' => self::lotPrice($l, $product),
                        'stock' => (int) $l->cantidad_disponible,
                        'label' => str_starts_with($l->codigo_lote, 'INGRESO-')
                            ? 'Ingreso ' . \Carbon\Carbon::parse(substr($l->codigo_lote, 8, 10))->format('d/m/Y')
                            : (str_starts_with($l->codigo_lote, 'CARGA-INICIAL') ? 'Stock inicial' : $l->codigo_lote),
                    ])
                    ->values()
                    ->all();

                $stock = array_sum(array_column($lots, 'stock'));

                // Producto "unidad" sin sueltos: ¿hay paquetes cerrados para abrir?
                $packsClosed = $stock > 0 ? 0 : Producto::where('id_producto_unidad', $product->id)
                    ->where('estado', 1)->get()->sum(fn ($p) => $p->availableStock());

                return [
                    'id' => $product->id,
                    'packs_closed' => $packsClosed,
                    'code' => $product->code,
                    'name' => $product->nombre . ($product->concentracion ? ' | ' . $product->concentracion : ''),
                    'laboratory' => $product->laboratory?->nombre,
                    'presentation' => $product->presentation?->nombre,
                    'label' => $product->code . ' - ' . $product->nombre .
                        ($product->concentracion ? ' | ' . $product->concentracion : '') .
                        ' | Stock: ' . $stock,
                    // Precio del lote que se vende primero (el más antiguo).
                    'sale_price' => $lots[0]['price'] ?? (float) $product->precio_venta,
                    'stock' => $stock,
                    'lots' => $lots,
                ];
            })
            ->toArray();
    }

    /**
     * Agrega un producto al detalle. Con $lotId se vende ese stock (precio de
     * ese ingreso); sin él, el lote más antiguo que quede disponible.
     */
    public function addProductToDetail($id, $lotId = null)
    {
        $product = Producto::findOrFail($id);

        // Lo que ya está en el detalle de cada lote (para pasar al siguiente
        // lote cuando el más antiguo ya se usó completo en esta venta).
        $usedByLot = [];
        foreach ($this->details as $d) {
            if (!empty($d['id_lote'])) {
                $usedByLot[$d['id_lote']] = ($usedByLot[$d['id_lote']] ?? 0) + (int) ($d['quantity'] ?? 0);
            }
        }

        $availableLot = $this->scopeToUserSucursal(Lote::where('id_producto', $product->id)
            ->where('estado', 1)
            ->where('cantidad_disponible', '>', 0)
            ->where(function ($q) {
                $q->whereNull('fecha_vencimiento')
                    ->orWhereDate('fecha_vencimiento', '>=', now()->toDateString());
            }))
            ->orderBy('id', 'asc') // primero el stock que ENTRÓ antes (el más antiguo)
            ->when($lotId, fn ($q) => $q->whereKey($lotId))
            ->get()
            ->first(fn ($l) => $l->cantidad_disponible - ($usedByLot[$l->id] ?? 0) > 0);

        if (!$availableLot && $lotId) {
            $this->dispatch('toast', type: 'warning', message: 'Ese stock ya está agregado completo o no tiene unidades.');
            return;
        }

        if (!$availableLot && !empty($usedByLot) && $this->scopeToUserSucursal(Lote::where('id_producto', $product->id)->where('cantidad_disponible', '>', 0))->exists()) {
            $this->dispatch('toast', type: 'warning', message: 'Ya agregó todo el stock disponible de este producto.');
            return;
        }

        if (!$availableLot) {
            // Producto "unidad" sin sueltos: ofrecer abrir un paquete cerrado.
            $pack = Producto::where('id_producto_unidad', $product->id)
                ->where('estado', 1)
                ->get()
                ->first(fn ($p) => $p->isPack() && $p->availableStock() > 0);

            if ($pack) {
                $this->dispatch(
                    'confirm-open-package',
                    packId: $pack->id,
                    unitId: $product->id,
                    packName: $pack->nombre,
                    factor: (int) $pack->unidades_por_paquete,
                    packsLeft: $pack->availableStock(),
                );
                return;
            }

            $this->dispatch('toast', type: 'warning', message: 'El producto no tiene lote disponible o no vencido.');
            return;
        }

        $lotExpirationLabel = optional($availableLot->fecha_vencimiento)->format('d/m/Y') ?? 'Sin vencimiento';
        $lotPrice = self::lotPrice($availableLot, $product);
        $lotLeft = $availableLot->cantidad_disponible - ($usedByLot[$availableLot->id] ?? 0);

        // Lo último agregado va arriba, para no tener que bajar a buscarlo.
        array_unshift($this->details, [
            'type' => 'producto',
            'id_producto' => $product->id,
            'id_lote' => $availableLot->id,
            'product_label' => $product->code . ' - ' . $product->nombre . ($product->concentracion ? ' | ' . $product->concentracion : ''),
            'lot_label' => $availableLot->codigo_lote . ' | Vence: ' . $lotExpirationLabel . ' | Stock: ' . $lotLeft,
            'quantity' => 1,
            'available' => $lotLeft,
            // Se cobra el precio del LOTE (cada ingreso tiene su propio precio).
            'sale_price' => $lotPrice,
            'subtotal' => $lotPrice,
        ]);

        $this->productSearch = '';
        $this->productResults = [];

        $this->calculateTotals();
    }

    /**
     * Abre 1 paquete cerrado (ej. Huggies x20) en unidades sueltas y agrega la
     * unidad al detalle. Lo puede hacer quien puede vender (Trabajador incluido).
     */
    public function openPackage($packId, $unitId)
    {
        if (!Auth::user()?->can('Crear Ventas')) {
            $this->dispatch('toast', type: 'error', message: 'No tiene permiso para abrir paquetes.');
            return;
        }

        try {
            $pack = Producto::findOrFail($packId);

            if ((int) $pack->id_producto_unidad !== (int) $unitId) {
                throw new \RuntimeException('El paquete no corresponde a ese producto.');
            }

            app(\App\Services\AperturaPaquete::class)->abrir($pack, Auth::id());

            $this->dispatch('toast', type: 'success', message: 'Paquete abierto: ' . $pack->unidades_por_paquete . ' unidades sueltas disponibles.');
            $this->addProductToDetail($unitId);
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo abrir el paquete. ' . $e->getMessage());
        }
    }

    public function updatedServiceSearch()
    {
        if (strlen(trim($this->serviceSearch)) < 2) {
            $this->serviceResults = [];
            return;
        }

        $this->serviceResults = Servicio::query()
            ->where('estado', 1)
            ->where('nombre', 'like', "%{$this->serviceSearch}%")
            ->limit(10)
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'label' => $service->nombre . ' | Bs. ' . number_format($service->precio, 2),
                    'sale_price' => (float) $service->precio,
                ];
            })
            ->toArray();
    }

    public function addServiceToDetail($id)
    {
        $service = Servicio::findOrFail($id);

        // Lo último agregado va arriba, para no tener que bajar a buscarlo.
        array_unshift($this->details, [
            'type' => 'servicio',
            'id_servicio' => $service->id,
            'product_label' => $service->nombre,
            'lot_label' => 'Servicio',
            'quantity' => 1,
            'available' => null,
            'sale_price' => (float) $service->precio,
            'subtotal' => (float) $service->precio,
        ]);

        $this->serviceSearch = '';
        $this->serviceResults = [];

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
            if (($detail['type'] ?? 'producto') === 'servicio') {
                if ((float) ($detail['quantity'] ?? 0) < 0.01) {
                    $this->details[$i]['quantity'] = 1;
                }
                continue;
            }

            $available = (int) ($detail['available'] ?? 0);
            $quantity = (int) ($detail['quantity'] ?? 0);

            if ($quantity < 1) {
                $this->details[$i]['quantity'] = 1;
            } elseif ($quantity > $available) {
                $this->details[$i]['quantity'] = $available;
                $this->dispatch(
                    'toast',
                    type: 'warning',
                    message: 'Solo hay ' . $available . ' unidades disponibles de "' . ($detail['product_label'] ?? 'este producto') . '". Se ajustó la cantidad al máximo disponible.'
                );
            }
        }

        $this->calculateTotals();
    }

    public function updatedFormIdMetodoPago()
    {
        $this->resetErrorBag('form.id_metodo_pago');
        if (!$this->isMixedPayment()) {
            $this->form->monto_qr = '';
        }
        $this->calculateTotals();
    }

    public function updatedFormMontoPagado()
    {
        $this->resetErrorBag('form.monto_pagado');
        $this->calculateTotals();
    }

    public function updatedFormMontoQr()
    {
        $this->resetErrorBag('form.monto_qr');
        $this->calculateTotals();
    }

    /** ¿El método elegido es Mixto (efectivo + QR)? */
    protected function isMixedPayment(): bool
    {
        if (!$this->form->id_metodo_pago) return false;

        return strtoupper((string) MetodoPago::whereKey($this->form->id_metodo_pago)->value('nombre')) === 'MIXTO';
    }

    /** Parte que se cobra en EFECTIVO (en Mixto = total - QR). */
    protected function cashDue(): float
    {
        if (!$this->isMixedPayment()) return (float) $this->form->total;

        $qr = is_numeric($this->form->monto_qr) ? (float) $this->form->monto_qr : 0;

        return max(0, round((float) $this->form->total - $qr, 2));
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

        // Por ahora no se manejan descuentos.
        $discount = 0;
        $this->form->descuento = 0;

        $this->form->subtotal = $subtotal;
        $this->form->total = $subtotal - $discount;

        // El cambio sale de lo que se paga en efectivo (en Mixto, solo esa parte).
        $amountPaid = (float) ($this->form->monto_pagado ?: 0);
        $due = $this->cashDue();
        $this->form->monto_cambio = $amountPaid > $due ? round($amountPaid - $due, 2) : 0;
    }

    /** Precio de un lote; si no tiene precio, el del producto. */
    public static function lotPrice(?Lote $lot, ?Producto $product = null): float
    {
        $price = (float) ($lot?->precio_venta ?? 0);

        if ($price <= 0) {
            $product ??= $lot ? Producto::find($lot->id_producto) : null;
            $price = (float) ($product?->precio_venta ?? 0);
        }

        return $price;
    }

    /**
     * El precio de venta no se puede modificar: siempre se toma el registrado
     * (precio del LOTE para productos —cada ingreso puede tener su precio—,
     * precio del servicio para servicios). Si el admin cambia el precio del
     * producto, se copia a sus lotes y se cobra el nuevo.
     */
    protected function applyRegisteredPrices(): void
    {
        foreach ($this->details as $i => $detail) {
            $price = ($detail['type'] ?? 'producto') === 'servicio'
                ? (float) Servicio::whereKey($detail['id_servicio'] ?? 0)->value('precio')
                : self::lotPrice(Lote::find($detail['id_lote'] ?? 0), Producto::find($detail['id_producto'] ?? 0));

            $this->details[$i]['sale_price'] = (float) $price;
        }
    }

    public function save()
    {
        // La fecha de venta es siempre la de hoy y no se puede cambiar.
        $this->form->fecha_venta = now()->toDateString();

        $this->validate($this->form->rules(), $this->form->messages());

        $this->applyRegisteredPrices();

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
            if (($detail['type'] ?? 'producto') === 'servicio') {
                if (empty($detail['id_servicio']) || (float) $detail['quantity'] <= 0 || (float) $detail['sale_price'] < 0) {
                    $this->dispatch('toast', type: 'warning', message: 'Revise el detalle de venta. Hay filas de servicio incompletas.');
                    return;
                }
                continue;
            }

            if (
                empty($detail['id_producto']) ||
                empty($detail['id_lote']) ||
                (int) $detail['quantity'] <= 0 ||
                (float) $detail['sale_price'] < 0
            ) {
                $this->dispatch('toast', type: 'warning', message: 'Revise el detalle de venta. Hay filas incompletas.');
                return;
            }
        }

        $this->calculateTotals();

        $amountPaid = $this->form->monto_pagado === '' ? null : (float) $this->form->monto_pagado;

        $paymentMethod = MetodoPago::findOrFail($this->form->id_metodo_pago);
        $methodName = strtoupper($paymentMethod->nombre);
        $total = round((float) $this->form->total, 2);

        // Reparto del cobro: efectivo (al cajón) y QR (a la cuenta).
        if ($methodName === 'EFECTIVO') {
            if ($amountPaid === null || $amountPaid < $total) {
                $this->addError('form.monto_pagado', 'El monto recibido debe ser mayor o igual al total.');
                $this->dispatch('toast', type: 'warning', message: 'El monto recibido debe ser mayor o igual al total de la venta.');
                return;
            }
            $cashPart = $total;
            $qrPart = 0.0;
        } elseif ($methodName === 'MIXTO') {
            $qrPart = is_numeric($this->form->monto_qr) ? round((float) $this->form->monto_qr, 2) : 0;
            if ($qrPart <= 0 || $qrPart >= $total) {
                $this->addError('form.monto_qr', 'En pago mixto el monto por QR debe ser mayor a 0 y menor al total (' . number_format($total, 2) . ').');
                $this->dispatch('toast', type: 'warning', message: 'Indique cuánto paga por QR (mayor a 0 y menor al total).');
                return;
            }
            $cashPart = round($total - $qrPart, 2);
            if ($amountPaid === null || $amountPaid < $cashPart) {
                $this->addError('form.monto_pagado', 'El efectivo recibido debe cubrir la parte en efectivo (' . number_format($cashPart, 2) . ').');
                $this->dispatch('toast', type: 'warning', message: 'El efectivo recibido debe cubrir la parte en efectivo (' . number_format($cashPart, 2) . ').');
                return;
            }
        } else { // QR
            $cashPart = 0.0;
            $qrPart = $total;
        }

        DB::beginTransaction();

        try {
            $this->calculateTotals();

            $cash = Caja::where('id', $cash->id)
                ->where('status', 'OPEN')
                ->lockForUpdate()
                ->firstOrFail();

            $sale = Venta::create([
                'id_cliente' => $this->form->id_cliente ?: null,
                'id_usuario' => Auth::id(),
                'id_caja' => $cash->id,
                'id_sucursal' => $cash->id_sucursal,
                'fecha_venta' => $this->form->fecha_venta,
                'id_metodo_pago' => $paymentMethod->id,
                'metodo_pago' => $paymentMethod->nombre,
                'subtotal' => $this->form->subtotal,
                'descuento' => $this->form->descuento ?: 0,
                'total' => $this->form->total,
                'monto_efectivo' => $cashPart,
                'monto_qr' => $qrPart,
                'monto_pagado' => $amountPaid,
                // El cambio se da solo sobre la parte en efectivo.
                'monto_cambio' => $amountPaid !== null ? max(0, round($amountPaid - $cashPart, 2)) : null,
                'observacion' => $this->form->observacion ?: null,
                'estado' => 1,
                'status' => 'ACTIVE',
            ]);

            foreach ($this->details as $detail) {
                if (($detail['type'] ?? 'producto') === 'servicio') {
                    DetalleVenta::create([
                        'id_venta' => $sale->id,
                        'id_servicio' => $detail['id_servicio'],
                        'cantidad_servicio' => $detail['quantity'],
                        'quantity' => (int) round($detail['quantity']) ?: 1,
                        'precio_venta' => $detail['sale_price'],
                        'subtotal' => $detail['subtotal'],
                    ]);

                    continue;
                }

                $lot = Lote::where('id', $detail['id_lote'])
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
                    'id_venta' => $sale->id,
                    'id_producto' => $detail['id_producto'],
                    'id_lote' => $detail['id_lote'],
                    'quantity' => $detail['quantity'],
                    'precio_venta' => $detail['sale_price'],
                    'subtotal' => $detail['subtotal'],
                ]);

                $lot->cantidad_disponible -= (int) $detail['quantity'];
                $lot->save();

                MovimientoInventario::create([
                    'id_producto' => $detail['id_producto'],
                    'id_lote' => $lot->id,
                    'id_usuario' => Auth::id(),
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
                'id_caja' => $cash->id,
                'id_usuario' => Auth::id(),
                'type' => 'INCOME',
                'concepto' => 'Venta #' . $sale->id,
                'monto' => $sale->total,
                'reference_type' => 'SALE',
                'reference_id' => $sale->id,
                'observacion' => 'Ingreso automático por venta.',
            ]);

            // Esperado = solo efectivo (el QR no entra al cajón).
            $cash->recalculateExpected();

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Venta registrada correctamente.');

            return redirect()->route('ventas');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar la venta. ' . $e->getMessage());
        }
    }

    /**
     * Edita solo el pago (método, monto QR/efectivo recibido) de una venta ya
     * registrada. El detalle de productos/servicios y el total no cambian.
     * Solo admin, y solo mientras la caja de esa venta siga ABIERTA.
     */
    public function saveEdit()
    {
        if (!Auth::user()?->hasRole('admin')) {
            $this->dispatch('toast', type: 'error', message: 'No tiene permiso para editar ventas.');
            return;
        }

        $this->validate($this->form->rules(), $this->form->messages());

        DB::beginTransaction();

        try {
            $sale = Venta::with('cashRegister')->where('id', $this->form->id)->lockForUpdate()->firstOrFail();

            if ($sale->status !== 'ACTIVE' || $sale->cashRegister?->status !== 'OPEN') {
                DB::rollBack();
                $this->dispatch('toast', type: 'warning', message: 'Esta venta ya no se puede editar: su caja está cerrada.');
                $this->redirectRoute('ventas.view', $sale->id, navigate: true);
                return;
            }

            $paymentMethod = MetodoPago::findOrFail($this->form->id_metodo_pago);
            $methodName = strtoupper($paymentMethod->nombre);
            $total = round((float) $sale->total, 2);
            $amountPaid = $this->form->monto_pagado === '' ? null : (float) $this->form->monto_pagado;

            if ($methodName === 'EFECTIVO') {
                if ($amountPaid === null || $amountPaid < $total) {
                    DB::rollBack();
                    $this->addError('form.monto_pagado', 'El monto recibido debe ser mayor o igual al total.');
                    return;
                }
                $cashPart = $total;
                $qrPart = 0.0;
            } elseif ($methodName === 'MIXTO') {
                $qrPart = is_numeric($this->form->monto_qr) ? round((float) $this->form->monto_qr, 2) : 0;
                if ($qrPart <= 0 || $qrPart >= $total) {
                    DB::rollBack();
                    $this->addError('form.monto_qr', 'En pago mixto el monto por QR debe ser mayor a 0 y menor al total (' . number_format($total, 2) . ').');
                    return;
                }
                $cashPart = round($total - $qrPart, 2);
                if ($amountPaid === null || $amountPaid < $cashPart) {
                    DB::rollBack();
                    $this->addError('form.monto_pagado', 'El efectivo recibido debe cubrir la parte en efectivo (' . number_format($cashPart, 2) . ').');
                    return;
                }
            } else { // QR
                $cashPart = 0.0;
                $qrPart = $total;
            }

            $sale->id_metodo_pago = $paymentMethod->id;
            $sale->metodo_pago = $paymentMethod->nombre;
            $sale->monto_efectivo = $cashPart;
            $sale->monto_qr = $qrPart;
            $sale->monto_pagado = $amountPaid;
            $sale->monto_cambio = $amountPaid !== null ? max(0, round($amountPaid - $cashPart, 2)) : null;
            $sale->save();

            $sale->cashRegister?->recalculateExpected();

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Pago de la venta actualizado correctamente.');

            $this->redirectRoute('ventas.view', $sale->id, navigate: true);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar la venta. ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('ventas');
    }

    public function cancelSale($id, $reason = null)
    {
        // El motivo de la cancelación es obligatorio.
        $reason = mb_substr(trim((string) $reason), 0, 255);

        if ($reason === '') {
            $this->dispatch('toast', type: 'warning', message: 'Debe escribir el motivo de la cancelación.');
            return;
        }

        DB::beginTransaction();

        try {
            $sale = Venta::with(['details.lot', 'invoice'])
                ->where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($sale->status === 'CANCELLED') {
                DB::rollBack();
                $this->dispatch('toast', type: 'warning', message: 'La venta ya está cancelada.');
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
                if ($detail->id_servicio) {
                    continue;
                }

                $lot = Lote::where('id', $detail->id_lote)
                    ->lockForUpdate()
                    ->firstOrFail();

                $stockBefore = $lot->cantidad_disponible;

                $lot->cantidad_disponible += $detail->quantity;
                $lot->save();

                MovimientoInventario::create([
                    'id_producto' => $detail->id_producto,
                    'id_lote' => $lot->id,
                    'id_usuario' => Auth::id(),
                    'tipo_movimiento' => 'RETURN',
                    'reference_type' => 'SALE_CANCEL',
                    'reference_id' => $sale->id,
                    'quantity' => $detail->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $lot->cantidad_disponible,
                    'descripcion' => 'Devolución de stock por anulación de venta #' . $sale->id,
                ]);
            }

            // La anulación se registra en la caja ORIGINAL de la venta (no en la
            // de quien anula: si el admin anula una venta del trabajador, la
            // caja del admin no tiene nada que ver).
            $cash = $sale->id_caja
                ? Caja::whereKey($sale->id_caja)->lockForUpdate()->first()
                : null;

            if ($cash) {
                MovimientoCaja::create([
                    'id_caja' => $cash->id,
                    'id_usuario' => Auth::id(),
                    'type' => 'EXPENSE',
                    'concepto' => 'Anulación venta #' . $sale->id,
                    'monto' => $sale->total,
                    'reference_type' => 'SALE_CANCEL',
                    'reference_id' => $sale->id,
                    'observacion' => 'Reversión automática por anulación de venta.',
                ]);
            }

            $sale->status = 'CANCELLED';
            $sale->estado = 0;
            $sale->cancelled_at = now();
            $sale->cancelled_by = Auth::id();
            $sale->cancel_reason = $reason;
            $sale->save();

            // Recalcula el efectivo esperado de la caja de la venta (ya sin ella).
            Caja::find($sale->id_caja)?->recalculateExpected();
            if ($cash && $cash->id !== $sale->id_caja) {
                $cash->recalculateExpected();
            }

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Venta cancelada, stock restaurado y caja ajustada.');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo cancelar la venta. ' . $e->getMessage());
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
