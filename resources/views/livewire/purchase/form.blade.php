<div class="mx-auto max-w-6xl p-6">
    <div class="mb-6">
        <a href="{{ route('purchases') }}" wire:navigate class="mb-2 inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
            <i class="bi bi-arrow-left"></i> Volver a compras
        </a>
        <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
            {{ $viewMode ? 'Ver compra' : ($form->id ? 'Editar compra' : 'Nueva compra') }}
        </h1>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid grid-cols-1 gap-4 px-6 py-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Proveedor <span class="text-red-600">*</span></label>

                @if ($selectedSupplierLabel)
                    <div class="flex items-center justify-between rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 dark:border-emerald-800 dark:bg-emerald-900/20">
                        <span class="text-sm text-emerald-700 dark:text-emerald-300">
                            {{ $selectedSupplierLabel }}
                        </span>

                        @unless($viewMode)
                            <button wire:click="clearSupplier" type="button" class="text-red-500 hover:text-red-700">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        @endunless
                    </div>
                @elseif(!$viewMode)
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live="supplierSearch"
                            placeholder="Buscar proveedor..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >

                        @if (!empty($supplierResults))
                            <div class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                                @foreach ($supplierResults as $supplier)
                                    <button
                                        type="button"
                                        wire:click="selectSupplier({{ $supplier['id'] }})"
                                        class="block w-full border-b border-gray-100 px-3 py-2 text-left text-sm hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-700"
                                    >
                                        {{ $supplier['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                @error('form.id_supplier')
                    <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Fecha compra <span class="text-red-600">*</span></label>
                <input type="date" wire:model="form.purchase_date" @disabled($viewMode)
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 disabled:bg-gray-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:disabled:bg-zinc-700">
                @error('form.purchase_date') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Factura <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="text" wire:model="form.invoice_number" @disabled($viewMode)
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 disabled:bg-gray-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:disabled:bg-zinc-700">
                @error('form.invoice_number') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2 xl:col-span-4">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Observación <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <textarea wire:model="form.observation" rows="2" @disabled($viewMode)
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 disabled:bg-gray-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:disabled:bg-zinc-700"></textarea>
                @error('form.observation') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
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
                            <div class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                                @foreach ($productResults as $product)
                                    <button
                                        type="button"
                                        wire:click="addProductToDetail({{ $product['id'] }})"
                                        class="block w-full border-b border-gray-100 px-3 py-2 text-left text-sm hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-700"
                                    >
                                        {{ $product['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endunless

            <div class="md:col-span-2 xl:col-span-4 overflow-x-auto rounded-xl border border-gray-200 dark:border-zinc-800">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-zinc-800">
                        <tr>
                            <th class="px-3 py-2 text-left text-zinc-700 dark:text-zinc-200">PRODUCTO</th>
                            <th class="px-3 py-2 text-left text-zinc-700 dark:text-zinc-200">LOTE</th>
                            <th class="px-3 py-2 text-left text-zinc-700 dark:text-zinc-200">VENCIMIENTO</th>
                            <th class="px-3 py-2 text-center text-zinc-700 dark:text-zinc-200">CANTIDAD</th>
                            <th class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-200">P. COMPRA</th>
                            <th class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-200">P. VENTA</th>
                            <th class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-200">SUBTOTAL</th>
                            @unless($viewMode)
                                <th class="px-3 py-2 text-center text-zinc-700 dark:text-zinc-200">ACCIONES</th>
                            @endunless
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                        @forelse($details as $index => $detail)
                            <tr class="bg-white dark:bg-zinc-900">
                                <td class="px-3 py-2 text-zinc-800 dark:text-zinc-100 min-w-[220px]">
                                    {{ $detail['product_label'] }}
                                </td>
                                <td class="px-3 py-2 min-w-[130px]">
                                    <input type="text" wire:model.live="details.{{ $index }}.batch_code" @disabled($viewMode)
                                        class="w-full rounded border border-gray-300 px-2 py-1 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                </td>
                                <td class="px-3 py-2 min-w-[140px]">
                                    <input type="date" wire:model.live="details.{{ $index }}.expiration_date" @disabled($viewMode)
                                        class="w-full rounded border border-gray-300 px-2 py-1 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                </td>
                                <td class="px-3 py-2 min-w-[100px]">
                                    <input type="number" min="1" wire:model.live="details.{{ $index }}.quantity" @disabled($viewMode)
                                        class="w-full rounded border border-gray-300 px-2 py-1 text-center dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                </td>
                                <td class="px-3 py-2 min-w-[120px]">
                                    <input type="number" step="0.01" min="0" wire:model.live="details.{{ $index }}.purchase_price" @disabled($viewMode)
                                        class="w-full rounded border border-gray-300 px-2 py-1 text-right dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                </td>
                                <td class="px-3 py-2 min-w-[120px]">
                                    <input type="number" step="0.01" min="0" wire:model.live="details.{{ $index }}.sale_price" @disabled($viewMode)
                                        class="w-full rounded border border-gray-300 px-2 py-1 text-right dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                </td>
                                <td class="px-3 py-2 text-right text-zinc-800 dark:text-zinc-100">
                                    {{ number_format((float)($detail['subtotal'] ?? 0), 2) }}
                                </td>
                                @unless($viewMode)
                                    <td class="px-3 py-2 text-center">
                                        <button type="button" wire:click="removeDetail({{ $index }})"
                                            class="text-red-500 hover:text-red-700">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                @endunless
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $viewMode ? 7 : 8 }}" class="px-4 py-4 text-center text-gray-500 dark:text-zinc-400">
                                    No hay productos en el detalle.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="xl:col-span-4 flex justify-end">
                <div class="w-full max-w-sm rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-zinc-800 dark:bg-zinc-800/50">
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-700 dark:text-zinc-200">Subtotal</span>
                        <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format((float)$form->subtotal, 2) }}</span>
                    </div>
                    <div class="mt-2 flex justify-between text-base">
                        <span class="font-semibold text-zinc-700 dark:text-zinc-200">Total</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ number_format((float)$form->total, 2) }}</span>
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
                <button wire:click="save"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                    Guardar
                </button>
            @endunless
        </div>
    </div>
</div>
