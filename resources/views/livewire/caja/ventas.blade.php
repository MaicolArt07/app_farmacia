<div class="space-y-3 bg-zinc-200 p-4 dark:bg-zinc-950">
    <style>@media (min-width: 768px) { .caja-ventas-kpis { grid-template-columns: repeat(5, minmax(0, 1fr)); } }</style>
    @php
        $difClass = fn ($d) => $d == 0 ? 'text-emerald-600 dark:text-emerald-400' : ($d < 0 ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400');
        $difText = fn ($d) => $d == 0 ? 'cuadra' : ($d < 0 ? 'faltante' : 'sobrante');
    @endphp

    {{-- Encabezado --}}
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h1 class="text-xl font-bold text-zinc-800 dark:text-zinc-100">
                <i class="bi bi-receipt mr-1 text-primary"></i> Ventas de la caja
            </h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                <strong>{{ $caja->user?->name }}</strong> ·
                abierta {{ \Carbon\Carbon::parse($caja->fecha_apertura)->format('d/m/Y') }} {{ $caja->created_at?->format('H:i') }}
                @if($caja->closed_at)
                    · cerrada {{ \Carbon\Carbon::parse($caja->closed_at)->format('d/m/Y H:i') }}
                @else
                    · <span class="font-semibold text-emerald-600">abierta</span>
                @endif
            </p>
        </div>
        <a href="{{ route('cash-registers') }}" wire:navigate
            class="rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200">
            <i class="bi bi-arrow-left"></i> Volver a cajas
        </a>
    </div>

    {{-- Resumen de la caja --}}
    <div class="caja-ventas-kpis grid grid-cols-2 gap-3">
        <div class="rounded border border-zinc-300 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Ventas</div>
            <div class="text-lg font-bold text-zinc-800 dark:text-zinc-100">{{ $totals['ventas'] }}
                @if($totals['canceladas'])<span class="text-xs font-normal text-red-500">+{{ $totals['canceladas'] }} cancel.</span>@endif
            </div>
        </div>
        <div class="rounded border border-zinc-300 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400"><i class="bi bi-cash-stack text-emerald-600"></i> Efectivo</div>
            <div class="text-lg font-bold text-zinc-800 dark:text-zinc-100">{{ number_format($totals['efectivo'], 2) }}</div>
        </div>
        <div class="rounded border border-zinc-300 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400"><i class="bi bi-qr-code text-sky-700"></i> QR</div>
            <div class="text-lg font-bold text-zinc-800 dark:text-zinc-100">{{ number_format($totals['qr'], 2) }}</div>
        </div>
        <div class="rounded border border-primary bg-white p-3 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Total vendido</div>
            <div class="text-lg font-bold text-primary">Bs {{ number_format($totals['total'], 2) }}</div>
        </div>
        <div class="rounded border border-zinc-300 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Unidades vendidas</div>
            <div class="text-lg font-bold text-zinc-800 dark:text-zinc-100">{{ $totals['unidades'] }}</div>
        </div>
    </div>

    {{-- Cierre (si está cerrada) --}}
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <div class="rounded border border-zinc-300 bg-white p-3 text-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Efectivo del cajón</div>
            <div class="flex justify-between text-zinc-700 dark:text-zinc-300"><span>Monto inicial</span><span>{{ number_format($b['opening'], 2) }}</span></div>
            <div class="flex justify-between text-zinc-700 dark:text-zinc-300"><span>+ Ventas en efectivo</span><span>{{ number_format($b['cash_sales'], 2) }}</span></div>
            @if($b['incomes'] > 0)<div class="flex justify-between text-zinc-700 dark:text-zinc-300"><span>+ Ingresos manuales</span><span>{{ number_format($b['incomes'], 2) }}</span></div>@endif
            @if($b['expenses'] > 0)<div class="flex justify-between text-zinc-700 dark:text-zinc-300"><span>− Egresos manuales</span><span>{{ number_format($b['expenses'], 2) }}</span></div>@endif
            <div class="mt-1 flex justify-between border-t border-gray-200 pt-1 font-bold text-zinc-800 dark:border-zinc-700 dark:text-zinc-100"><span>= Esperado</span><span>{{ number_format($b['expected_cash'], 2) }}</span></div>
            @if($caja->monto_cierre !== null)
                @php $d = (float) $caja->diferencia; @endphp
                <div class="flex justify-between text-zinc-700 dark:text-zinc-300"><span>Contado</span><span>{{ number_format($caja->monto_cierre, 2) }}</span></div>
                <div class="flex justify-between font-bold {{ $difClass($d) }}"><span>Diferencia ({{ $difText($d) }})</span><span>{{ $d > 0 ? '+' : '' }}{{ number_format($d, 2) }}</span></div>
            @endif
        </div>
        <div class="rounded border border-zinc-300 bg-white p-3 text-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">QR (cuenta)</div>
            <div class="flex justify-between text-zinc-700 dark:text-zinc-300"><span>QR según el sistema</span><span>{{ number_format($b['qr_sales'], 2) }}</span></div>
            @if($caja->monto_qr_verificado !== null)
                @php $dq = (float) $caja->diferencia_qr; @endphp
                <div class="flex justify-between text-zinc-700 dark:text-zinc-300"><span>QR verificado al cerrar</span><span>{{ number_format($caja->monto_qr_verificado, 2) }}</span></div>
                <div class="flex justify-between font-bold {{ $difClass($dq) }}"><span>Diferencia QR ({{ $difText($dq) }})</span><span>{{ $dq > 0 ? '+' : '' }}{{ number_format($dq, 2) }}</span></div>
            @endif
            @if($caja->observacion)
                <div class="mt-2 whitespace-pre-line rounded bg-zinc-100 px-2 py-1 text-xs text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"><i class="bi bi-journal-text text-primary"></i> {{ $caja->observacion }}</div>
            @endif
        </div>
    </div>

    {{-- Filtros --}}
    <div class="flex flex-wrap items-center justify-between gap-2 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex overflow-hidden rounded border border-zinc-400 dark:border-zinc-600">
            @foreach(['ventas' => ['Ventas (' . $sales->count() . ')', 'bi-receipt'], 'productos' => ['Productos vendidos (' . $products->count() . ')', 'bi-box-seam']] as $key => [$label, $icon])
                <button type="button" wire:click="$set('tab', '{{ $key }}')"
                    class="cursor-pointer px-3 py-1.5 text-sm {{ $tab === $key ? 'bg-primary text-white' : 'bg-white text-zinc-700 hover:bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-200' }} {{ !$loop->first ? 'border-l border-zinc-400 dark:border-zinc-600' : '' }}">
                    <i class="bi {{ $icon }}"></i> {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="flex overflow-hidden rounded border border-zinc-400 dark:border-zinc-600">
                @foreach(['' => 'Todos', 'EFECTIVO' => 'Efectivo', 'QR' => 'QR', 'MIXTO' => 'Mixto'] as $key => $label)
                    <button type="button" wire:click="setPaymentFilter('{{ $key }}')"
                        class="cursor-pointer px-3 py-1.5 text-sm {{ $paymentFilter === $key ? 'bg-primary text-white' : 'bg-white text-zinc-700 hover:bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-200' }} {{ !$loop->first ? 'border-l border-zinc-400 dark:border-zinc-600' : '' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <label class="flex cursor-pointer items-center gap-1 text-sm text-zinc-700 dark:text-zinc-200">
                <input type="checkbox" wire:model.live="showCancelled"> Ver canceladas
            </label>
            <input type="text" wire:model.live.debounce.400ms="search" autocomplete="off"
                placeholder="Buscar producto o N° de venta..."
                class="w-full rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 focus:border-primary focus:outline-none md:w-80 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
        </div>
    </div>

    @if($tab === 'ventas')
        {{-- Cada venta con su detalle --}}
        @forelse($sales as $sale)
            @php $cancel = $sale->status === 'CANCELLED'; @endphp
            <div class="overflow-hidden rounded border bg-white dark:bg-zinc-900 {{ $cancel ? 'border-red-300 dark:border-red-900/50' : 'border-zinc-300 dark:border-zinc-700' }}">
                <div class="flex flex-wrap items-center justify-between gap-2 px-3 py-2 {{ $cancel ? 'bg-red-50 dark:bg-red-900/20' : 'bg-zinc-100 dark:bg-zinc-800' }}">
                    <div class="text-sm text-zinc-800 dark:text-zinc-100">
                        <strong>Venta #{{ $sale->id }}</strong>
                        <span class="text-zinc-500 dark:text-zinc-400">· {{ $sale->created_at?->format('d/m/Y H:i') }} · {{ $sale->user?->name }}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium
                            {{ strtoupper($sale->metodo_pago) === 'QR' ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300' : (strtoupper($sale->metodo_pago) === 'MIXTO' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300') }}">
                            {{ $sale->metodo_pago }}
                            @if($sale->isMixed()) (Ef. {{ number_format($sale->cashPart(), 2) }} · QR {{ number_format($sale->qrPart(), 2) }}) @endif
                        </span>
                        @if($sale->monto_pagado !== null && $sale->cashPart() > 0)
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">recibió {{ number_format($sale->monto_pagado, 2) }} · cambio {{ number_format((float) $sale->monto_cambio, 2) }}</span>
                        @endif
                        @if($cancel)
                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">Cancelada</span>
                        @endif
                        @if((float) $sale->monto_devuelto > 0)
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
                                title="Total original {{ number_format($sale->total + $sale->monto_devuelto, 2) }}">
                                Devolución −{{ number_format($sale->monto_devuelto, 2) }}
                            </span>
                        @endif
                        <span class="font-bold {{ $cancel ? 'text-zinc-400' : 'text-zinc-900 dark:text-zinc-100' }}" @if($cancel) style="text-decoration: line-through" @endif>Bs {{ number_format($sale->total, 2) }}</span>
                        @if(!$cancel && $sale->details->sum('quantity') > 0)
                            <button type="button" wire:click="openReturn({{ $sale->id }})"
                                class="cursor-pointer rounded border border-amber-400 bg-white px-2 py-0.5 text-xs font-medium text-amber-700 hover:bg-amber-50 dark:bg-zinc-800 dark:text-amber-300"
                                title="Devolver productos de esta venta">
                                <i class="bi bi-arrow-return-left"></i> Devolver
                            </button>
                        @endif
                    </div>
                </div>

                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                        @foreach($sale->details as $d)
                            @php $hit = $search !== '' && str_contains(mb_strtolower(($d->product?->nombre ?? $d->service?->nombre ?? '') . ' ' . $d->product?->code), mb_strtolower(trim($search))); @endphp
                            <tr class="{{ $hit ? 'bg-sky-100 dark:bg-sky-900/30' : '' }}">
                                <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                                    @if($d->service)
                                        <span class="rounded bg-amber-100 px-1.5 text-xs text-amber-700">Servicio</span> {{ $d->service->nombre }}
                                    @else
                                        {{ $d->product?->nombre ?? 'Producto eliminado' }}
                                        <span class="text-xs text-zinc-400">{{ $d->product?->laboratory?->nombre }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-1.5 text-xs text-zinc-500 dark:text-zinc-400">{{ $d->lot?->codigo_lote }}</td>
                                <td class="px-3 py-1.5 text-right text-zinc-700 dark:text-zinc-300" style="white-space: nowrap">
                                    {{ (int) $d->quantity }} × {{ number_format($d->precio_venta, 2) }}
                                    @if($d->cantidad_devuelta > 0)
                                        <div class="text-xs text-amber-700 dark:text-amber-300">devolvió {{ $d->cantidad_devuelta }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-1.5 text-right font-semibold text-zinc-800 dark:text-zinc-100" style="width: 7rem">{{ number_format($d->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @foreach($sale->returns as $ret)
                    <div class="border-t border-amber-300 bg-amber-50 px-3 py-1.5 text-xs text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                        <i class="bi bi-arrow-return-left"></i>
                        Devolución {{ $ret->created_at?->format('d/m/Y H:i') }} por {{ $ret->user?->name }}:
                        {{ $ret->details->map(fn ($x) => $x->cantidad . '× ' . ($x->product?->nombre ?? $x->service?->nombre))->implode(', ') }}
                        = <strong>{{ number_format($ret->monto, 2) }} Bs</strong>.
                        <strong>Motivo:</strong> {{ $ret->motivo }}
                    </div>
                @endforeach

                @if($cancel)
                    <div class="border-t border-red-200 px-3 py-1.5 text-xs text-red-700 dark:border-red-900/50 dark:text-red-300">
                        <i class="bi bi-x-circle"></i> Cancelada {{ $sale->cancelled_at?->format('d/m/Y H:i') }} por {{ $sale->cancelledBy?->name ?? '-' }}.
                        <strong>Motivo:</strong> {{ $sale->cancel_reason ?? '-' }}
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded border border-zinc-300 bg-white p-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                No hay ventas que coincidan.
            </div>
        @endforelse
    @else
        {{-- Resumen de productos vendidos en esta caja --}}
        <div class="overflow-auto rounded border border-zinc-300 dark:border-zinc-700">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-300 dark:bg-zinc-800">
                    <tr>
                        <th class="px-3 py-1.5 text-left font-semibold text-zinc-700 dark:text-zinc-200">PRODUCTO / SERVICIO</th>
                        <th class="px-3 py-1.5 text-right font-semibold text-zinc-700 dark:text-zinc-200">CANTIDAD</th>
                        <th class="px-3 py-1.5 text-right font-semibold text-zinc-700 dark:text-zinc-200">VENTAS</th>
                        <th class="px-3 py-1.5 text-right font-semibold text-zinc-700 dark:text-zinc-200">TOTAL (Bs)</th>
                        <th class="px-3 py-1.5 text-right font-semibold text-zinc-700 dark:text-zinc-200">GANANCIA (Bs)</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900">
                    @forelse($products as $p)
                        <tr class="border-b border-zinc-200 dark:border-zinc-800">
                            <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $p->nombre }} <span class="font-mono text-xs text-zinc-400">{{ $p->code }}</span></td>
                            <td class="px-3 py-1.5 text-right font-bold text-zinc-800 dark:text-zinc-100">{{ (int) $p->cantidad }}</td>
                            <td class="px-3 py-1.5 text-right text-zinc-600 dark:text-zinc-300">{{ $p->ventas }}</td>
                            <td class="px-3 py-1.5 text-right font-semibold text-zinc-800 dark:text-zinc-100">{{ number_format($p->total, 2) }}</td>
                            <td class="px-3 py-1.5 text-right text-emerald-700 dark:text-emerald-300">{{ $p->code ? number_format($p->total - $p->costo, 2) : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-4 text-center text-zinc-500">Sin productos vendidos.</td></tr>
                    @endforelse
                </tbody>
                @if($products->isNotEmpty())
                    <tfoot class="bg-zinc-100 font-bold dark:bg-zinc-800">
                        <tr>
                            <td class="px-3 py-2 text-zinc-800 dark:text-zinc-100">TOTAL</td>
                            <td class="px-3 py-2 text-right text-zinc-800 dark:text-zinc-100">{{ $totals['unidades'] }}</td>
                            <td></td>
                            <td class="px-3 py-2 text-right text-zinc-800 dark:text-zinc-100">{{ number_format($products->sum('total'), 2) }}</td>
                            <td class="px-3 py-2 text-right text-emerald-700 dark:text-emerald-300">{{ number_format($products->filter(fn ($p) => $p->code)->sum(fn ($p) => $p->total - $p->costo), 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        <p class="text-xs text-zinc-500 dark:text-zinc-400">Solo ventas activas (las canceladas no cuentan). La ganancia usa el precio de compra del lote vendido.</p>
    @endif

    {{-- ================= Ventana: devolver productos ================= --}}
    @if($returnSale && $returnPreview)
        @php $pv = $returnPreview; $c = $pv['caja']; @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-zinc-800">
                    <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                        <i class="bi bi-arrow-return-left mr-1 text-amber-600"></i>Devolver productos — venta #{{ $returnSale->id }}
                    </h2>
                    <button wire:click="closeReturn" class="text-gray-500 hover:text-red-500 dark:text-zinc-400"><i class="bi bi-x-lg"></i></button>
                </div>

                <div class="max-h-[70vh] space-y-4 overflow-y-auto px-6 py-5">
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">
                        Pagada con <strong>{{ $returnSale->metodo_pago }}</strong> · total actual <strong>Bs {{ number_format($returnSale->total, 2) }}</strong>.
                        Indique cuántas unidades devolvió el cliente de cada producto.
                    </p>

                    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-3 py-1.5 text-left text-xs text-zinc-600 dark:text-zinc-300">PRODUCTO</th>
                                    <th class="px-3 py-1.5 text-right text-xs text-zinc-600 dark:text-zinc-300">VENDIDO</th>
                                    <th class="px-3 py-1.5 text-center text-xs text-zinc-600 dark:text-zinc-300">DEVUELVE</th>
                                    <th class="px-3 py-1.5 text-right text-xs text-zinc-600 dark:text-zinc-300">MONTO</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                                @foreach($returnSale->details as $d)
                                    @php $q = (int) ($returnQty[$d->id] ?? 0); @endphp
                                    <tr>
                                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                                            {{ $d->product?->nombre ?? ('SERVICIO: ' . $d->service?->nombre) }}
                                            <div class="text-xs text-zinc-400">{{ $d->lot?->codigo_lote }}</div>
                                        </td>
                                        <td class="px-3 py-1.5 text-right text-zinc-700 dark:text-zinc-300">{{ $d->quantity }} × {{ number_format($d->precio_venta, 2) }}</td>
                                        <td class="px-3 py-1.5 text-center">
                                            @if($d->quantity > 0)
                                                <input type="number" min="0" max="{{ $d->quantity }}" step="1"
                                                    wire:model.live.debounce.300ms="returnQty.{{ $d->id }}"
                                                    class="w-20 rounded border border-gray-300 px-2 py-1 text-right dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            @else
                                                <span class="text-xs text-zinc-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-1.5 text-right font-semibold {{ $q > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-zinc-400' }}">
                                            {{ number_format(min($q, $d->quantity) * $d->precio_venta, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @error('returnQty') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                    @if($pv['amount'] > 0)
                        <div class="rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-zinc-700 dark:border-amber-800 dark:bg-amber-900/20 dark:text-zinc-200">
                            <div class="flex justify-between font-bold"><span>Monto devuelto</span><span>Bs {{ number_format($pv['amount'], 2) }}</span></div>
                            <div class="flex justify-between text-xs"><span>Sale de</span>
                                <span>@if($pv['cash'] > 0) efectivo {{ number_format($pv['cash'], 2) }} @endif @if($pv['qr'] > 0) {{ $pv['cash'] > 0 ? '+' : '' }} QR {{ number_format($pv['qr'], 2) }} @endif</span></div>
                            <div class="flex justify-between"><span>La venta queda en</span><span class="font-semibold">Bs {{ number_format($pv['new_total'], 2) }}</span></div>
                            @if($c)
                                <div class="mt-2 border-t border-amber-300 pt-2 text-xs dark:border-amber-800">
                                    <div class="mb-1 font-semibold">Efecto en la caja #{{ $c['id'] }} {{ $c['closed'] ? '(CERRADA: se recalcula)' : '(abierta)' }}</div>
                                    <div class="flex justify-between"><span>Efectivo esperado</span><span>{{ number_format($c['expected_before'], 2) }} → <strong>{{ number_format($c['expected_after'], 2) }}</strong></span></div>
                                    @if($c['diff_before'] !== null)
                                        <div class="flex justify-between"><span>Diferencia del cierre</span><span>{{ $c['diff_before'] > 0 ? '+' : '' }}{{ number_format($c['diff_before'], 2) }} → <strong>{{ $c['diff_after'] > 0 ? '+' : '' }}{{ number_format($c['diff_after'], 2) }}</strong></span></div>
                                    @endif
                                    @if($pv['qr'] > 0)
                                        <div class="flex justify-between"><span>QR del sistema</span><span>{{ number_format($c['qr_before'], 2) }} → <strong>{{ number_format($c['qr_after'], 2) }}</strong></span></div>
                                    @endif
                                </div>
                            @endif
                            <div class="mt-2 text-xs">Las unidades vuelven al stock de su mismo lote y queda una nota en la observación de la caja.</div>
                        </div>
                    @endif

                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Motivo <span class="text-red-600">*</span></label>
                        <textarea wire:model="returnReason" rows="2" maxlength="500"
                            placeholder="Ej: El cliente devolvió 1 Dolocalma y 1 Cotrimoxazol; el saldo lo usó en otra compra."
                            class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-zinc-800 dark:text-zinc-100 {{ $errors->has('returnReason') ? 'border-red-500' : 'border-gray-300 dark:border-zinc-700' }}"></textarea>
                        @error('returnReason') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
                    <button wire:click="closeReturn"
                        class="cursor-pointer rounded-lg border border-red-300 bg-white px-4 py-2 text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-800 dark:text-red-400">Cancelar</button>
                    <button wire:click="saveReturn" @disabled($pv['amount'] <= 0)
                        class="cursor-pointer rounded-lg border border-amber-500 bg-white px-4 py-2 text-amber-700 hover:bg-amber-50 disabled:opacity-50 dark:bg-zinc-800 dark:text-amber-300">
                        <i class="bi bi-check2"></i> Confirmar devolución
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
