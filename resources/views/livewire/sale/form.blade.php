<div class="mx-auto max-w-6xl p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('sales') }}" wire:navigate class="mb-2 inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                <i class="bi bi-arrow-left"></i> Volver a ventas
            </a>
            <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
                {{ $viewMode ? 'Ver venta' : 'Nueva venta' }}
            </h1>
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
                    Tienes una caja abierta desde el {{ $openCash->opening_date?->format('d/m/Y') }} que ya venció su vigencia.
                    Ciérrala o amplía su tiempo de vida para poder seguir vendiendo.
                </span>
                <div class="flex shrink-0 gap-2">
                    <button type="button" wire:click="extendCash"
                        class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">
                        Ampliar vigencia
                    </button>
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
                            <div class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                                @foreach ($clientResults as $client)
                                    <button
                                        type="button"
                                        wire:click="selectClient({{ $client['id'] }})"
                                        class="block w-full border-b border-gray-100 px-3 py-2 text-left text-sm hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-700"
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

                @error('form.id_client')
                    <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Fecha venta <span class="text-red-600">*</span></label>
                <input type="date" wire:model="form.sale_date" @disabled($viewMode)
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 disabled:bg-gray-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:disabled:bg-zinc-700">
                @error('form.sale_date') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Método de pago <span class="text-red-600">*</span></label>
                <select wire:model="form.payment_method" @disabled($viewMode)
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 disabled:bg-gray-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:disabled:bg-zinc-700">
                    <option value="EFECTIVO">Efectivo</option>
                    <option value="QR">QR</option>
                    <option value="TARJETA">Tarjeta</option>
                    <option value="TRANSFERENCIA">Transferencia</option>
                    <option value="MIXTO">Mixto</option>
                </select>
                @error('form.payment_method') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            @unless($viewMode)
            <div class="md:col-span-2 xl:col-span-4">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Agregar producto</label>
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live="productSearch"
                        placeholder="Buscar producto por código, barras, nombre o genérico..."
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >

                    @if (!empty($productResults))
                        <div class="absolute z-10 mt-1 max-h-72 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                            @foreach ($productResults as $product)
                                <button
                                    type="button"
                                    wire:click="addProductToDetail({{ $product['id'] }})"
                                    class="block w-full border-b border-gray-100 px-3 py-2 text-left text-sm hover:bg-gray-100 dark:border-zinc-700 dark:hover:bg-zinc-700"
                                >
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $product['name'] }}</span>
                                        <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $product['stock'] > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                            Stock: {{ $product['stock'] }}
                                        </span>
                                    </div>
                                    <div class="mt-0.5 flex items-center justify-between gap-2 text-xs text-gray-500 dark:text-zinc-400">
                                        <span>Laboratorio: {{ $product['laboratory'] ?? 'Sin laboratorio' }} • Presentación: {{ $product['presentation'] ?? 'Sin presentación' }}</span>
                                        <span class="shrink-0 font-mono text-[11px] text-gray-400 dark:text-zinc-500">{{ $product['code'] }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @endunless

            <div class="md:col-span-2 xl:col-span-4 overflow-x-auto rounded-xl border border-gray-200 dark:border-zinc-800">
                <table class="min-w-full border-collapse text-sm">
                    <thead class="bg-gray-100 dark:bg-zinc-800">
                        <tr>
                            <th class="border border-gray-200 px-3 py-2 text-left text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">PRODUCTO</th>
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
                                    {{ $detail['product_label'] }}
                                </td>

                                <td class="border border-gray-200 px-3 py-2 align-middle text-zinc-700 dark:border-zinc-800 dark:text-zinc-300 min-w-[220px]">
                                    {{ $detail['lot_label'] }}
                                </td>

                                <td class="border border-gray-200 px-3 py-2 text-center align-middle text-zinc-700 dark:border-zinc-800 dark:text-zinc-300">
                                    {{ $detail['available'] ?? 0 }}
                                </td>

                                <td class="border border-gray-200 px-3 py-2 align-middle dark:border-zinc-800 min-w-[100px]">
                                    <input type="number" min="1" max="{{ $detail['available'] ?? 1 }}" wire:model.live="details.{{ $index }}.quantity" @disabled($viewMode)
                                        class="w-full rounded border border-gray-300 px-2 py-1 text-center dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                </td>

                                <td class="border border-gray-200 px-3 py-2 align-middle dark:border-zinc-800 min-w-[120px]">
                                    <input type="number" step="0.01" min="0" wire:model.live="details.{{ $index }}.sale_price" @disabled($viewMode)
                                        class="w-full rounded border border-gray-300 px-2 py-1 text-right dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
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
                                    No hay productos en el detalle.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="md:col-span-2 xl:col-span-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Observación <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                    <textarea wire:model="form.observation" rows="3" @disabled($viewMode)
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 disabled:bg-gray-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:disabled:bg-zinc-700"></textarea>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-zinc-800 dark:bg-zinc-800/50">
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-700 dark:text-zinc-200">Subtotal</span>
                        <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format((float)$form->subtotal, 2) }}</span>
                    </div>

                    <div class="mt-3">
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Descuento <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                        <input type="number" step="0.01" min="0" wire:model.live="form.discount" @disabled($viewMode)
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-right dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                    </div>

                    <div class="mt-3 flex justify-between text-base">
                        <span class="font-semibold text-zinc-700 dark:text-zinc-200">Total</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ number_format((float)$form->total, 2) }}</span>
                    </div>

                    <div class="mt-4 border-t border-gray-200 pt-3 dark:border-zinc-700">
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                            Monto recibido
                            @if(strtoupper($form->payment_method) === 'EFECTIVO')
                                <span class="text-red-600">*</span>
                            @else
                                <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span>
                            @endif
                        </label>
                        <input type="number" step="0.01" min="0" wire:model.live="form.amount_paid" @disabled($viewMode)
                            placeholder="Ej: 200"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-right dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                        @error('form.amount_paid') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-3 flex justify-between text-base">
                        <span class="font-semibold text-zinc-700 dark:text-zinc-200">Cambio</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ number_format((float)$form->change_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
            <button wire:click="cancel"
                class="rounded-lg border border-gray-300 px-4 py-2 text-zinc-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                {{ $viewMode ? 'Cerrar' : 'Cancelar' }}
            </button>

            @unless($viewMode)
                @can('Crear Ventas')
                <button wire:click="save" @disabled(!$openCash || $cashExpired)
                    class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600">
                    Guardar
                </button>
                @endcan
            @endunless
        </div>
    </div>
</div>
