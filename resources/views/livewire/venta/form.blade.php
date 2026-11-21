<div class="mx-auto max-w-6xl p-6"
    x-data
    {{-- Producto "unidad" sin sueltos: confirmar apertura de un paquete cerrado --}}
    x-on:confirm-open-package.window="
        Swal.fire({
            title: 'No hay unidades sueltas',
            html: '¿Abrir <b>1 paquete</b> de <b>' + $event.detail.packName + '</b>?<br>Se convierte en <b>' + $event.detail.factor + ' unidades</b> sueltas.<br><small>Quedan ' + $event.detail.packsLeft + ' paquete(s) cerrado(s).</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, abrir paquete',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
        }).then((r) => { if (r.isConfirmed) $wire.openPackage($event.detail.packId, $event.detail.unitId) })
    ">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('ventas') }}" wire:navigate class="mb-2 inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                <i class="bi bi-arrow-left"></i> Volver a ventas
            </a>
            <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
                {{ $editMode ? 'Editar pago de venta' : ($viewMode ? 'Ver venta' : 'Nueva venta') }}
            </h1>
            @if($editMode)
                <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                    <i class="bi bi-info-circle mr-1"></i>Solo se puede cambiar el método de pago y los montos. Los productos/servicios de la venta no se modifican aquí.
                </p>
            @endif
        </div>
    </div>

    @unless($viewMode)
        @if (!$openCash)
            <div class="mb-4 flex flex-col gap-2 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300 md:flex-row md:items-center md:justify-between">
                <span><i class="bi bi-exclamation-triangle-fill mr-1"></i> Debes abrir tu caja primero para llevar el control de tus ventas.</span>
                <a href="{{ route('cash-registers') }}" wire:navigate
                    class="shrink-0 rounded-lg bg-red-600 px-3 py-1.5 text-center text-xs font-semibold text-white hover:bg-red-700">
                    Abrir caja
                </a>
            </div>
        @elseif ($cashExpired)
            <div class="mb-4 flex flex-col gap-2 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300 md:flex-row md:items-center md:justify-between">
                <span>
                    <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                    Tienes una caja abierta desde el {{ $openCash->fecha_apertura?->format('d/m/Y') }} que ya venció su vigencia.
                    Ciérrala o amplía su tiempo de vida para poder seguir vendiendo.
                </span>
                <div class="flex shrink-0 gap-2">
                    @can('Editar Caja')
                    <button type="button" wire:click="extendCash"
                        class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">
                        Ampliar vigencia
                    </button>
                    @endcan
                    <a href="{{ route('cash-registers') }}" wire:navigate
                        class="rounded-lg border border-amber-400 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100 dark:text-amber-300 dark:hover:bg-amber-900/40">
                        Cerrar caja
                    </a>
                </div>
            </div>
        @endif
    @endunless

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid grid-cols-1 gap-4 px-6 py-5 md:grid-cols-2 xl:grid-cols-4">

            @php $showClient = $viewMode && $selectedClientLabel; @endphp

            {{-- Cliente: oculto por ahora (todas las ventas van a consumidor final).
                 Solo se muestra al ver una venta antigua que sí tenga cliente. --}}
            @if($showClient)
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Cliente <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional, deje vacío para consumidor final)</span></label>

                @if ($selectedClientLabel)
                    <div class="flex items-center justify-between rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 dark:border-emerald-800 dark:bg-emerald-900/20">
                        <span class="text-sm text-emerald-700 dark:text-emerald-300">
                            {{ $selectedClientLabel }}
                        </span>

                        @unless($viewMode)
                        <button wire:click="clearClient" type="button" class="text-red-500 hover:text-red-700">
                            <i class="bi bi-x-circle"></i>
                        </button>
                        @endunless
                    </div>
                @elseif(!$viewMode)
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live="clientSearch"
                            placeholder="Buscar cliente o dejar vacío consumidor final..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >

                        @if (!empty($clientResults))
                            <div class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-zinc-300 bg-zinc-100 shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                                @foreach ($clientResults as $client)
                                    <button
                                        type="button"
                                        wire:click="selectClient({{ $client['id'] }})"
                                        class="block w-full border-b border-zinc-300 px-3 py-2 text-left text-sm hover:bg-zinc-200 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-700"
                                    >
                                        {{ $client['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                        Consumidor final
                    </div>
                @endif

                @error('form.id_cliente')
                    <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>
            @endif

            {{-- Fecha de venta: siempre la de hoy, visible pero no editable. --}}
            <div class="{{ $showClient ? '' : 'xl:col-span-2' }}">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Fecha venta</label>
                <input type="date" wire:model="form.fecha_venta" disabled
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-600 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:disabled:bg-zinc-700">
                @error('form.fecha_venta') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="{{ $showClient ? '' : 'xl:col-span-2' }}">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Método de pago <span class="text-red-600">*</span></label>
                <select wire:model.live="form.id_metodo_pago" @disabled($viewMode && !$editMode)
                    class="w-full rounded-lg border bg-white px-3 py-2 disabled:bg-gray-100 dark:bg-zinc-800 dark:text-zinc-100 dark:disabled:bg-zinc-700 {{ $errors->has('form.id_metodo_pago') ? 'border-red-500' : 'border-gray-300 dark:border-zinc-700' }}">
                    <option value="">Seleccione Efectivo, QR o Mixto...</option>
                    @foreach($paymentMethods as $method)
                        <option value="{{ $method->id }}">{{ $method->nombre }}</option>
                    @endforeach
                </select>
                @error('form.id_metodo_pago') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            @unless($viewMode)
            {{-- Producto y servicio en dos columnas --}}
            <div class="md:col-span-2 xl:col-span-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-bold uppercase tracking-wide text-emerald-700 dark:text-emerald-400">
                    <i class="bi bi-capsule mr-1"></i>Agregar producto
                </label>
                <div class="relative">
                    {{-- debounce: busca cuando deja de escribir (evita que una respuesta
                         vieja llegue después y deje la lista vacía o desactualizada). --}}
                    <input
                        type="text"
                        wire:model.live.debounce.350ms="productSearch"
                        autocomplete="off" spellcheck="false"
                        placeholder="Buscar producto por código, barras, nombre o genérico..."
                        class="w-full rounded-lg border border-emerald-300 bg-white dark:border-emerald-800 px-3 py-2 text-zinc-800 focus:border-blue-500 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100"
                    >

                    @if (!empty($productResults))
                        <div wire:key="product-results-{{ md5($productSearch) }}" class="absolute z-10 mt-1 max-h-80 w-full overflow-y-auto rounded-lg border border-zinc-300 bg-zinc-100 shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                            @foreach ($productResults as $product)
                                <div wire:key="product-result-{{ $product['id'] }}">
                                {{-- Con stock: una fila por cada stock/ingreso, con su propio precio. --}}
                                @if (!empty($product['lots']))
                                    @foreach ($product['lots'] as $lot)
                                        <button
                                            type="button"
                                            wire:click="addProductToDetail({{ $product['id'] }}, {{ $lot['id'] }})"
                                            class="block w-full border-b border-zinc-300 px-3 py-2 text-left text-sm hover:bg-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-700"
                                        >
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $product['name'] }}</span>
                                                <span class="flex shrink-0 items-center" style="gap: .25rem">
                                                    <span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-bold text-sky-700 dark:bg-sky-900/30 dark:text-sky-300">
                                                        Bs {{ number_format($lot['price'], 2) }}
                                                    </span>
                                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                                        Stock: {{ $lot['stock'] }}
                                                    </span>
                                                </span>
                                            </div>
                                            <div class="mt-0.5 flex items-center justify-between gap-2 text-xs text-gray-500 dark:text-zinc-400">
                                                <span>
                                                    @if (count($product['lots']) > 1)
                                                        <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $lot['label'] }}</span>
                                                        @if ($loop->first)<span class="text-emerald-700 dark:text-emerald-300">(se vende primero)</span>@endif
                                                        •
                                                    @endif
                                                    Laboratorio: {{ $product['laboratory'] ?? 'Sin laboratorio' }} • Presentación: {{ $product['presentation'] ?? 'Sin presentación' }}
                                                </span>
                                                <span class="shrink-0 font-mono text-[11px] text-gray-400 dark:text-zinc-500">{{ $product['code'] }}</span>
                                            </div>
                                        </button>
                                    @endforeach
                                @else
                                <button
                                    type="button"
                                    wire:click="addProductToDetail({{ $product['id'] }})"
                                    class="block w-full border-b border-zinc-300 px-3 py-2 text-left text-sm hover:bg-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-700"
                                >
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $product['name'] }}</span>
                                        @if ($product['stock'] <= 0 && ($product['packs_closed'] ?? 0) > 0)
                                            <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                                <i class="bi bi-box-seam"></i> Sin sueltos · {{ $product['packs_closed'] }} {{ $product['packs_closed'] == 1 ? 'paquete cerrado' : 'paquetes cerrados' }}
                                            </span>
                                        @else
                                        <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $product['stock'] > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                            Stock: {{ $product['stock'] }}
                                        </span>
                                        @endif
                                    </div>
                                    <div class="mt-0.5 flex items-center justify-between gap-2 text-xs text-gray-500 dark:text-zinc-400">
                                        <span>Laboratorio: {{ $product['laboratory'] ?? 'Sin laboratorio' }} • Presentación: {{ $product['presentation'] ?? 'Sin presentación' }}</span>
                                        <span class="shrink-0 font-mono text-[11px] text-gray-400 dark:text-zinc-500">{{ $product['code'] }}</span>
                                    </div>
                                </button>

                                {{-- Sin stock: equivalentes con el mismo principio activo. --}}
                                @if (!empty($product['equivalents']))
                                    <div class="border-b border-zinc-300 bg-sky-50 px-3 py-2 dark:border-zinc-700 dark:bg-sky-950/30">
                                        <div class="mb-1 text-xs font-bold uppercase tracking-wide text-sky-700 dark:text-sky-300">
                                            <i class="bi bi-arrow-left-right mr-1"></i>Equivalentes con stock
                                            <span class="font-normal normal-case text-zinc-500 dark:text-zinc-400">· {{ $product['generic'] }}</span>
                                        </div>
                                        @foreach ($product['equivalents'] as $eq)
                                            <button
                                                type="button"
                                                wire:click="addProductToDetail({{ $eq['id'] }}, {{ $eq['lot_id'] }})"
                                                class="mt-1 flex w-full cursor-pointer items-center justify-between gap-2 rounded-md border border-sky-200 bg-white px-2 py-1.5 text-left text-sm transition hover:border-sky-500 hover:bg-sky-100 hover:shadow-sm dark:border-sky-900 dark:bg-zinc-800 dark:hover:border-sky-500 dark:hover:bg-sky-900/50"
                                                title="{{ $eq['same_via'] ? 'Misma forma: se puede cambiar directo' : 'Otra presentación: confirme con el cliente' }}"
                                            >
                                                <span class="min-w-0">
                                                    @if ($eq['same_via'])
                                                        <i class="bi bi-check-circle-fill text-emerald-600 dark:text-emerald-400"></i>
                                                    @else
                                                        <i class="bi bi-arrow-return-right text-amber-600 dark:text-amber-400"></i>
                                                    @endif
                                                    <span class="text-zinc-800 dark:text-zinc-100">{{ $eq['name'] }}</span>
                                                    @if (!$eq['same_via'])
                                                        <span class="text-xs text-amber-700 dark:text-amber-300">(otra forma{{ $eq['via'] ? ': ' . $eq['via'] : '' }})</span>
                                                    @elseif ($eq['same_conc'])
                                                        <span class="text-xs text-emerald-700 dark:text-emerald-300">(misma dosis)</span>
                                                    @endif
                                                </span>
                                                <span class="flex shrink-0 items-center" style="gap: .25rem">
                                                    <span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-bold text-sky-700 dark:bg-sky-900/30 dark:text-sky-300">Bs {{ number_format($eq['price'], 2) }}</span>
                                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Stock: {{ $eq['stock'] }}</span>
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif ($product['stock'] <= 0 && ($product['packs_closed'] ?? 0) <= 0 && $product['generic'])
                                    <div class="border-b border-zinc-300 px-3 py-1.5 text-xs text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                        Sin equivalentes con stock ({{ $product['generic'] }}).
                                    </div>
                                @endif
                                @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Servicio: se usa poco, así que solo se muestra un botón que abre el buscador. --}}
            <div x-data="{ open: false }">
                <label class="mb-1 block text-sm font-bold uppercase tracking-wide text-amber-600 dark:text-amber-400">
                    <i class="bi bi-bandaid mr-1"></i>Agregar servicio
                    <span class="text-xs font-normal text-gray-400 dark:text-zinc-500" style="text-transform: none; letter-spacing: normal">(Ej: inyectable, curación, nebulización...)</span>
                </label>

                <button type="button" x-show="!open"
                    @click="open = true; $nextTick(() => $refs.serviceInput.focus())"
                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 font-semibold text-amber-700 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300 cursor-pointer">
                    <i class="bi bi-plus-circle"></i> Agregar servicio
                </button>

                <div x-show="open" x-cloak class="relative">
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            x-ref="serviceInput"
                            wire:model.live.debounce.350ms="serviceSearch" autocomplete="off"
                            @keydown.escape="open = false; $wire.set('serviceSearch', '')"
                            placeholder="Buscar servicio por nombre..."
                            class="w-full rounded-lg border border-amber-300 bg-white px-3 py-2 text-zinc-800 focus:border-blue-500 focus:outline-none dark:border-amber-800 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                        <button type="button" title="Cerrar"
                            @click="open = false; $wire.set('serviceSearch', '')"
                            class="shrink-0 rounded-lg border border-gray-300 px-3 py-2 text-gray-500 hover:text-red-500 dark:border-zinc-700 dark:text-zinc-400 cursor-pointer">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    @if (!empty($serviceResults))
                        <div class="absolute z-10 mt-1 max-h-72 w-full overflow-y-auto rounded-lg border border-zinc-300 bg-zinc-100 shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                            @foreach ($serviceResults as $service)
                                <button
                                    type="button"
                                    wire:click="addServiceToDetail({{ $service['id'] }})"
                                    @click="open = false"
                                    class="block w-full border-b border-zinc-300 px-3 py-2 text-left text-sm hover:bg-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-700"
                                >
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $service['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            </div>
            @endunless

            <div class="md:col-span-2 xl:col-span-4 overflow-x-auto rounded-xl border border-gray-200 dark:border-zinc-800">
                <table class="min-w-full border-collapse text-sm">
                    <thead class="bg-gray-100 dark:bg-zinc-800">
                        <tr>
                            <th class="border border-gray-200 px-3 py-2 text-left text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">PRODUCTO / SERVICIO</th>
                            <th class="border border-gray-200 px-3 py-2 text-left text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">LOTE</th>
                            <th class="border border-gray-200 px-3 py-2 text-center text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">STOCK</th>
                            <th class="border border-gray-200 px-3 py-2 text-center text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">CANTIDAD</th>
                            <th class="border border-gray-200 px-3 py-2 text-right text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">PRECIO</th>
                            <th class="border border-gray-200 px-3 py-2 text-right text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">SUBTOTAL</th>
                            @unless($viewMode)
                            <th class="border border-gray-200 px-3 py-2 text-center text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">ACCIONES</th>
                            @endunless
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($details as $index => $detail)
                            <tr class="bg-white dark:bg-zinc-900">
                                <td class="border border-gray-200 px-3 py-2 align-middle text-zinc-800 dark:border-zinc-800 dark:text-zinc-100 min-w-[220px]">
                                    @if (($detail['type'] ?? 'producto') === 'servicio')
                                        <span class="mr-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">SERVICIO</span>
                                    @else
                                        <span class="mr-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">PRODUCTO</span>
                                    @endif
                                    {{ $detail['product_label'] }}
                                </td>

                                <td class="border border-gray-200 px-3 py-2 align-middle text-zinc-700 dark:border-zinc-800 dark:text-zinc-300 min-w-[220px]">
                                    {{ $detail['lot_label'] }}
                                </td>

                                <td class="border border-gray-200 px-3 py-2 text-center align-middle text-zinc-700 dark:border-zinc-800 dark:text-zinc-300">
                                    {{ ($detail['type'] ?? 'producto') === 'servicio' ? '-' : ($detail['available'] ?? 0) }}
                                </td>

                                <td class="border border-gray-200 px-3 py-2 align-middle dark:border-zinc-800 min-w-[100px]">
                                    @if (($detail['type'] ?? 'producto') === 'servicio')
                                        <input type="number" min="1" step="1" wire:model.blur="details.{{ $index }}.quantity" @disabled($viewMode)
                                            class="w-full rounded border border-gray-300 px-2 py-1 text-center dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                    @else
                                        <input type="number" min="1" max="{{ $detail['available'] ?? 1 }}" wire:model.blur="details.{{ $index }}.quantity" @disabled($viewMode)
                                            class="w-full rounded border border-gray-300 px-2 py-1 text-center dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                    @endif
                                </td>

                                {{-- Precio registrado: no editable. --}}
                                <td class="border border-gray-200 px-3 py-2 text-right align-middle text-zinc-800 dark:border-zinc-800 dark:text-zinc-100">
                                    {{ number_format((float)($detail['sale_price'] ?? 0), 2) }}
                                </td>

                                <td class="border border-gray-200 px-3 py-2 text-right align-middle text-zinc-800 dark:border-zinc-800 dark:text-zinc-100">
                                    {{ number_format((float)($detail['subtotal'] ?? 0), 2) }}
                                </td>

                                @unless($viewMode)
                                <td class="border border-gray-200 px-3 py-2 text-center align-middle dark:border-zinc-800">
                                    <button type="button" wire:click="removeDetail({{ $index }})"
                                        class="text-red-500 hover:text-red-700">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                                @endunless
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $viewMode ? 6 : 7 }}" class="border border-gray-200 px-4 py-4 text-center text-gray-500 dark:border-zinc-800 dark:text-zinc-400">
                                    No hay productos ni servicios en el detalle.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="md:col-span-2 xl:col-span-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                {{-- Sin campo de observación al vender: el único texto libre es el
                     motivo, que se pide al cancelar. Al ver una venta se muestra aquí. --}}
                <div>
                    @if($viewMode && $saleInfo)
                        <div class="space-y-2 rounded-xl border p-4 text-sm {{ $saleInfo['cancelled'] ? 'border-red-300 bg-red-50 dark:border-red-800 dark:bg-red-900/20' : 'border-gray-200 bg-gray-50 dark:border-zinc-800 dark:bg-zinc-800/50' }}">
                            <div class="flex justify-between">
                                <span class="text-zinc-600 dark:text-zinc-300">Registrada</span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $saleInfo['fecha_hora'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-600 dark:text-zinc-300">Estado</span>
                                @if($saleInfo['cancelled'])
                                    <span class="rounded-full bg-red-100 px-3 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">Cancelado</span>
                                @else
                                    <span class="rounded-full bg-emerald-100 px-3 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Vendido</span>
                                @endif
                            </div>
                            @if($saleInfo['cancelled'])
                                @if($saleInfo['cancelled_at'])
                                    <div class="flex justify-between">
                                        <span class="text-zinc-600 dark:text-zinc-300">Cancelada el</span>
                                        <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $saleInfo['cancelled_at'] }}</span>
                                    </div>
                                @endif
                                <div class="border-t border-red-200 pt-2 dark:border-red-800">
                                    <span class="block text-zinc-600 dark:text-zinc-300">Motivo de cancelación</span>
                                    <p class="mt-1 font-medium text-red-700 dark:text-red-300">{{ $saleInfo['cancel_reason'] ?: '-' }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-zinc-800 dark:bg-zinc-800/50">
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-700 dark:text-zinc-200">Subtotal</span>
                        <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format((float)$form->subtotal, 2) }}</span>
                    </div>

                    <div class="mt-3 flex justify-between text-base">
                        <span class="font-semibold text-zinc-700 dark:text-zinc-200">Total</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ number_format((float)$form->total, 2) }}</span>
                    </div>

                    @php
                        $methodName = strtoupper((string) optional($paymentMethods->firstWhere('id', $form->id_metodo_pago))->nombre);
                        $isMixed = $methodName === 'MIXTO';
                        $qrTyped = is_numeric($form->monto_qr) ? (float) $form->monto_qr : 0;
                        $cashDue = $isMixed ? max(0, (float) $form->total - $qrTyped) : (float) $form->total;
                    @endphp

                    @if($isMixed)
                        {{-- Pago MIXTO: parte por QR + parte en efectivo --}}
                        <div class="mt-4 border-t border-gray-200 pt-3 dark:border-zinc-700">
                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                                <i class="bi bi-qr-code mr-1 text-sky-600"></i>Monto por QR <span class="text-red-600">*</span>
                            </label>
                            <input type="number" step="0.01" min="0" wire:model.live.debounce.400ms="form.monto_qr" @disabled($viewMode && !$editMode)
                                placeholder="Ej: 40"
                                class="w-full rounded-lg border bg-white px-3 py-2 text-right dark:bg-zinc-900 dark:text-zinc-100 {{ $errors->has('form.monto_qr') ? 'border-red-500' : 'border-gray-300 dark:border-zinc-700' }}">
                            @error('form.monto_qr') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="mt-3 flex justify-between rounded-lg bg-sky-100 px-3 py-2 text-sm text-sky-700 dark:bg-sky-900/30 dark:text-sky-300">
                            <span>A cobrar en efectivo (total − QR)</span>
                            <span class="font-bold">{{ number_format($cashDue, 2) }}</span>
                        </div>
                    @endif

                    <div class="mt-4 border-t border-gray-200 pt-3 dark:border-zinc-700">
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                            {{ $isMixed ? 'Efectivo recibido' : 'Monto recibido' }}
                            @if(in_array($methodName, ['EFECTIVO', 'MIXTO'], true))
                                <span class="text-red-600">*</span>
                            @else
                                <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span>
                            @endif
                        </label>
                        <input type="number" step="0.01" min="0" wire:model.live="form.monto_pagado" @disabled($viewMode && !$editMode)
                            placeholder="{{ $isMixed ? 'Ej: 50' : 'Ej: 200' }}"
                            class="w-full rounded-lg border bg-white px-3 py-2 text-right dark:bg-zinc-900 dark:text-zinc-100 {{ $errors->has('form.monto_pagado') ? 'border-red-500' : 'border-gray-300 dark:border-zinc-700' }}">
                        @error('form.monto_pagado') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-3 flex justify-between text-base">
                        <span class="font-semibold text-zinc-700 dark:text-zinc-200">Cambio</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ number_format((float)$form->monto_cambio, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
            <button wire:click="cancel"
                class="rounded-lg border border-red-300 bg-white px-4 py-2 text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20 cursor-pointer">
                {{ $viewMode ? 'Cerrar' : 'Cancelar' }}
            </button>

            @if($editMode)
                <button wire:click="saveEdit"
                    class="rounded-lg border border-primary bg-white px-4 py-2 text-primary hover:bg-primary/10 dark:bg-zinc-800 dark:hover:bg-primary/20 cursor-pointer">
                    Guardar cambios
                </button>
            @elseif(!$viewMode)
                @can('Crear Ventas')
                <button wire:click="save" @disabled(!$openCash || $cashExpired)
                    class="rounded-lg border border-primary bg-white px-4 py-2 text-primary hover:bg-primary/10 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-800 dark:hover:bg-primary/20 cursor-pointer">
                    Guardar
                </button>
                @endcan
            @endif
        </div>
    </div>
</div>
