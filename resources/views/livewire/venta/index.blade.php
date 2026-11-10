<div class="flex flex-col bg-zinc-200 p-4 dark:bg-zinc-950"
    x-data="{
        {{-- Pide el motivo (obligatorio) antes de cancelar una venta. --}}
        askCancel(id) {
            Swal.fire({
                title: '¿Cancelar esta venta?',
                text: 'Se devolverá el stock y se ajustará la caja.',
                icon: 'warning',
                input: 'textarea',
                inputLabel: 'Motivo de la cancelación',
                inputPlaceholder: 'Escribe por qué se cancela la venta...',
                inputAttributes: { maxlength: 255 },
                showCancelButton: true,
                confirmButtonText: 'Sí, cancelar venta',
                cancelButtonText: 'Volver',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                inputValidator: (v) => (!v || !v.trim()) ? 'Debes escribir el motivo de la cancelación.' : undefined,
            }).then((r) => { if (r.isConfirmed) $wire.cancelSale(id, r.value) })
        },
    }">
    @if (!$openCash)
        <div class="mb-3 flex flex-col gap-2 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300 md:flex-row md:items-center md:justify-between">
            <span><i class="bi bi-exclamation-triangle-fill mr-1"></i> Debes abrir tu caja primero para llevar el control de tus ventas.</span>
            <a href="{{ route('cash-registers') }}" wire:navigate
                class="shrink-0 rounded bg-red-600 px-3 py-1.5 text-center text-xs font-semibold text-white hover:bg-red-700">
                Abrir caja
            </a>
        </div>
    @elseif ($cashExpired)
        <div class="mb-3 flex flex-col gap-2 rounded border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300 md:flex-row md:items-center md:justify-between">
            <span>
                <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                Tienes una caja abierta desde el {{ $openCash->fecha_apertura?->format('d/m/Y') }} que ya venció su vigencia.
                Ciérrala o amplía su tiempo de vida para poder seguir vendiendo.
            </span>
            <div class="flex shrink-0 gap-2">
                @can('Editar Caja')
                <button type="button" wire:click="extendCash"
                    class="rounded bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">
                    Ampliar vigencia
                </button>
                @endcan
                <a href="{{ route('cash-registers') }}" wire:navigate
                    class="rounded border border-amber-400 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100 dark:text-amber-300 dark:hover:bg-amber-900/40">
                    Cerrar caja
                </a>
            </div>
        </div>
    @endif

    <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
        @can('Crear Ventas')
        <a href="{{ route('ventas.create') }}" wire:navigate
            class="flex items-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
            <i class="bi bi-bag-plus-fill text-primary"></i> Nueva venta
        </a>
        @endcan

        <div class="flex w-full flex-wrap items-center justify-end gap-2 md:w-auto">
            {{-- Filtro por método de pago --}}
            @php
                $chips = ['' => ['Todos', 'bi-list-ul'], 'EFECTIVO' => ['Efectivo', 'bi-cash-stack'], 'QR' => ['QR', 'bi-qr-code'], 'MIXTO' => ['Mixto', 'bi-shuffle']];
            @endphp
            <div class="flex overflow-hidden rounded border border-zinc-400 dark:border-zinc-600">
                @foreach($chips as $key => [$label, $icon])
                    <button type="button" wire:click="setPaymentFilter('{{ $key }}')"
                        class="cursor-pointer px-3 py-1.5 text-sm {{ $paymentFilter === $key ? 'bg-primary text-white' : 'bg-white text-zinc-700 hover:bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700' }} {{ !$loop->first ? 'border-l border-zinc-400 dark:border-zinc-600' : '' }}">
                        <i class="bi {{ $icon }}"></i> {{ $label }}
                    </button>
                @endforeach
            </div>

            @if($isAdmin)
                <div class="flex items-center gap-1">
                    <input type="date" wire:model.live="dateFrom"
                        class="rounded border border-zinc-400 bg-white px-2 py-1.5 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">a</span>
                    <input type="date" wire:model.live="dateTo"
                        class="rounded border border-zinc-400 bg-white px-2 py-1.5 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                </div>
            @endif

            <div class="relative w-full md:w-80">
                <i class="bi bi-search absolute text-zinc-400" style="left: .65rem; top: 50%; transform: translateY(-50%)"></i>
                <input
                    type="text"
                    wire:model.live.debounce.400ms="search"
                    autocomplete="off"
                    placeholder="Buscar por producto, N° de venta o método..."
                    class="w-full rounded border border-zinc-400 bg-white py-1.5 pr-3 text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                    style="padding-left: 2rem"
                >
            </div>
        </div>
    </div>

    @if(trim($search) !== '' || $paymentFilter !== '' || $dateFrom !== '' || $dateTo !== '')
        <div class="mb-3 flex flex-wrap items-center gap-2 rounded border border-gray-200 bg-sky-100 px-3 py-2 text-sm text-sky-700 dark:border-zinc-700 dark:bg-sky-900/30 dark:text-sky-300">
            <i class="bi bi-funnel-fill"></i>
            Mostrando ventas
            @if(trim($search) !== '') que contienen <strong>"{{ trim($search) }}"</strong> @endif
            @if($paymentFilter !== '') pagadas con <strong>{{ ucfirst(strtolower($paymentFilter)) }}</strong> @endif
            @if($dateFrom !== '' || $dateTo !== '')
                entre <strong>{{ $dateFrom !== '' ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'el inicio' }}</strong>
                y <strong>{{ $dateTo !== '' ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'hoy' }}</strong>
            @endif
            — los totales de abajo son solo de estas ventas.
            <button type="button" wire:click="$set('search', ''); setPaymentFilter(''); clearDateFilter()"
                class="ml-auto cursor-pointer text-xs underline hover:no-underline">Quitar filtros</button>
        </div>
    @endif

    {{-- Totales del día (caja abierta), sin contar ventas canceladas --}}
    <div class="mb-3 grid grid-cols-1 gap-3 md:grid-cols-3">
        <div class="rounded border border-zinc-300 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                <i class="bi bi-cash-stack mr-1 text-emerald-600 dark:text-emerald-400"></i>Total en efectivo
            </div>
            <div class="mt-1 text-xl font-bold text-zinc-800 dark:text-zinc-100">Bs {{ number_format($totals['efectivo'], 2) }}</div>
        </div>
        <div class="rounded border border-zinc-300 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                <i class="bi bi-qr-code mr-1 text-sky-700 dark:text-sky-300"></i>Total por QR
            </div>
            <div class="mt-1 text-xl font-bold text-zinc-800 dark:text-zinc-100">Bs {{ number_format($totals['qr'], 2) }}</div>
        </div>
        <div class="rounded border border-primary bg-white px-4 py-3 dark:bg-zinc-900">
            <div class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                <i class="bi bi-calculator mr-1 text-primary"></i>Total vendido
                <span style="text-transform: none">({{ $totals['cantidad'] }} {{ $totals['cantidad'] === 1 ? 'venta' : 'ventas' }})</span>
            </div>
            <div class="mt-1 text-2xl font-bold text-primary">Bs {{ number_format($totals['total'], 2) }}</div>
        </div>
    </div>

    <div class="hidden overflow-auto rounded border border-zinc-300 md:block dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Fecha</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Productos</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Pago</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-right font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Total</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Estado</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Opciones</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($sales as $item)
                    <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ \Carbon\Carbon::parse($item->fecha_venta)->format('d/m/Y') }}
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->created_at?->format('H:i') }}</span>
                            <div class="text-xs text-zinc-400">Venta #{{ $item->id }}</div>
                        </td>

                        {{-- Qué se vendió (lo que coincide con la búsqueda, resaltado) --}}
                        <td class="px-3 py-1.5 text-xs text-zinc-700 dark:text-zinc-300" style="max-width: 22rem">
                            @php
                                $term = mb_strtolower(trim($search));
                                $lines = $item->details->map(function ($d) use ($term) {
                                    $name = $d->product?->nombre ?? $d->service?->nombre ?? '—';
                                    $hit = $term !== '' && (str_contains(mb_strtolower($name), $term)
                                        || str_contains(mb_strtolower((string) $d->product?->code), $term));
                                    return ['text' => ((int) $d->quantity) . '× ' . $name, 'hit' => $hit];
                                })->sortByDesc('hit')->values();
                            @endphp
                            @foreach($lines->take(3) as $line)
                                <div class="truncate {{ $line['hit'] ? 'font-bold text-primary' : '' }}" title="{{ $line['text'] }}">{{ $line['text'] }}</div>
                            @endforeach
                            @if($lines->count() > 3)
                                <div class="text-zinc-400">+{{ $lines->count() - 3 }} más</div>
                            @endif
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->metodo_pago }}
                            @if($item->isMixed())
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Efectivo {{ number_format($item->cashPart(), 2) }} · QR {{ number_format($item->qrPart(), 2) }}
                                </div>
                            @endif
                        </td>

                        <td class="px-3 py-1.5 text-right text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->total, 2) }}
                        </td>

                        <td class="px-3 py-1.5 text-center">
                            @if($item->status === 'CANCELLED')
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                    Cancelado
                                </span>
                                @if($item->cancel_reason)
                                    <div class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $item->cancel_reason }}</div>
                                @endif
                            @else
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    Vendido
                                </span>
                            @endif
                        </td>

                        <td class="px-3 py-1.5">
                            <div class="flex justify-center gap-3">
                                <a href="{{ route('ventas.view', $item->id) }}" wire:navigate
                                    class="cursor-pointer text-zinc-500 transition hover:text-primary dark:text-zinc-400">
                                    <i class="bi bi-eye text-base"></i>
                                </a>

                                @if($isAdmin && $item->status === 'ACTIVE' && $item->cashRegister?->status === 'OPEN')
                                    <a href="{{ route('ventas.edit', $item->id) }}" wire:navigate
                                        class="cursor-pointer text-zinc-500 transition hover:text-amber-600 dark:text-zinc-400"
                                        title="Editar pago de la venta (caja aún abierta)">
                                        <i class="bi bi-pencil text-base"></i>
                                    </a>
                                @endif

                                @can('Cambiar Estado Ventas')
                                    @if($item->status !== 'CANCELLED')
                                        <button
                                            type="button"
                                            x-on:click.prevent="askCancel({{ $item->id }})"
                                            class="cursor-pointer text-zinc-500 transition hover:text-red-600 dark:text-zinc-400"
                                            title="Cancelar venta">
                                            <i class="bi bi-arrow-counterclockwise text-base"></i>
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            @if(trim($search) !== '' || $paymentFilter !== '')
                                Ninguna venta de hoy coincide con el filtro.
                            @else
                                No hay ventas registradas hoy en tu caja abierta.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if($totals['cantidad'] > 0)
            {{-- Totales en una sola fila, cada uno en su propia columna para que no queden juntos --}}
            <tfoot class="bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <td class="border-t border-zinc-400 px-3 py-2 text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">
                        <i class="bi bi-cash-stack mr-1 text-emerald-600 dark:text-emerald-400"></i>Efectivo:
                        <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($totals['efectivo'], 2) }}</span>
                    </td>
                    <td class="border-t border-zinc-400 px-3 py-2 text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">
                        <i class="bi bi-qr-code mr-1 text-sky-700 dark:text-sky-300"></i>QR:
                        <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($totals['qr'], 2) }}</span>
                    </td>
                    <td class="border-t border-zinc-400 px-3 py-2 text-base font-bold text-zinc-800 dark:border-zinc-600 dark:text-zinc-100">
                        TOTAL VENDIDO
                    </td>
                    <td class="border-t border-zinc-400 px-3 py-2 text-right text-base font-bold text-zinc-900 dark:border-zinc-600 dark:text-zinc-100">
                        {{ number_format($totals['total'], 2) }}
                    </td>
                    <td colspan="2" class="border-t border-zinc-400 dark:border-zinc-600"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <div class="flex flex-col gap-2 md:hidden">
        @forelse ($sales as $item)
            <div x-data="{ open: false }" class="rounded border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <button type="button" x-on:click.stop="open = !open"
                        class="flex h-6 w-6 shrink-0 cursor-pointer items-center justify-center rounded-full border border-primary text-primary transition hover:bg-primary hover:text-white">
                        <i class="bi text-xs" :class="open ? 'bi-dash' : 'bi-plus'"></i>
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-100">
                            #{{ $item->id }} ·
                            {{ $item->details->map(fn ($d) => ((int) $d->quantity) . '× ' . ($d->product?->nombre ?? $d->service?->nombre ?? '—'))->implode(', ') }}
                        </p>
                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ \Carbon\Carbon::parse($item->fecha_venta)->format('d/m/Y') }} {{ $item->created_at?->format('H:i') }} &middot; {{ number_format($item->total, 2) }}</p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3" onclick="event.stopPropagation()">
                        <a href="{{ route('ventas.view', $item->id) }}" wire:navigate
                            class="cursor-pointer text-zinc-500 transition hover:text-primary dark:text-zinc-400">
                            <i class="bi bi-eye text-base"></i>
                        </a>

                        @if($isAdmin && $item->status === 'ACTIVE' && $item->cashRegister?->status === 'OPEN')
                            <a href="{{ route('ventas.edit', $item->id) }}" wire:navigate
                                class="cursor-pointer text-zinc-500 transition hover:text-amber-600 dark:text-zinc-400"
                                title="Editar pago de la venta (caja aún abierta)">
                                <i class="bi bi-pencil text-base"></i>
                            </a>
                        @endif

                        @can('Cambiar Estado Ventas')
                            @if($item->status !== 'CANCELLED')
                                <button
                                    type="button"
                                    x-on:click.prevent="askCancel({{ $item->id }})"
                                    class="cursor-pointer text-zinc-500 transition hover:text-red-600 dark:text-zinc-400"
                                    title="Cancelar venta">
                                    <i class="bi bi-arrow-counterclockwise text-base"></i>
                                </button>
                            @endif
                        @endcan
                    </div>
                </div>

                <div x-show="open" x-collapse x-cloak onclick="event.stopPropagation()" class="mt-2 grid grid-cols-2 gap-2 border-t border-zinc-200 pt-2 pl-8 dark:border-zinc-800">
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Pago</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">
                            {{ $item->metodo_pago }}
                            @if($item->isMixed())
                                <span class="block text-xs text-zinc-500 dark:text-zinc-400">Ef. {{ number_format($item->cashPart(), 2) }} · QR {{ number_format($item->qrPart(), 2) }}</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Estado</span>
                        <p>
                            @if($item->status === 'CANCELLED')
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">Cancelado</span>
                            @else
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Vendido</span>
                            @endif
                        </p>
                    </div>
                    @if($item->status === 'CANCELLED' && $item->cancel_reason)
                        <div class="col-span-2">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Motivo de cancelación</span>
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $item->cancel_reason }}</p>
                        </div>
                    @endif
                    @if($item->client)
                        <div class="col-span-2">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">CI</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->client?->person?->ci ?? '-' }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded border border-zinc-300 bg-white px-3 py-4 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                No hay ventas registradas hoy en tu caja abierta.
            </div>
        @endforelse
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $sales->links() }}
    </div>
</div>