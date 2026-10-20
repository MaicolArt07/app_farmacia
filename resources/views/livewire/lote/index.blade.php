<div class="p-6">
    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <button type="button" wire:click="create"
            class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2 text-center text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600">
            <i class="bi bi-box2-heart mr-1"></i> Nuevo lote
        </button>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por lote, producto, código, vencimiento..."
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none md:w-[28rem] dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-400"
        >
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">PRODUCTO</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">LOTE</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">VENCIMIENTO</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">P. COMPRA</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">P. VENTA</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">INGRESO</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">DISPONIBLE</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">UBICACIÓN</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">ESTADO</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">OPCIONES</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                @forelse ($lots as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/70">
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->product?->nombre ?? '-' }}
                            <div class="text-xs text-gray-500 dark:text-zinc-400">
                                {{ $item->product?->code ?? '-' }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->codigo_lote }}
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->fecha_vencimiento ? $item->fecha_vencimiento->format('d/m/Y') : 'Sin vencimiento' }}
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->precio_compra, 2) }}
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->precio_venta, 2) }}
                        </td>

                        <td class="px-4 py-3 text-center text-zinc-800 dark:text-zinc-100">
                            {{ $item->cantidad_ingresada }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $item->cantidad_disponible > 0 ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $item->cantidad_disponible }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->ubicacion ?: '-' }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $item->estado ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $item->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex justify-center gap-3">
                                <button type="button" wire:click="edit({{ $item->id }})"
                                    class="cursor-pointer text-yellow-500 transition hover:text-yellow-600 dark:text-yellow-400 dark:hover:text-yellow-300">
                                    <i class="bi bi-pencil-square text-lg"></i>
                                </button>

                                <button
                                    type="button"
                                    x-on:click.prevent="confirmAction({
                                        text: '¿Deseas {{ $item->estado ? 'desactivar' : 'activar' }} este registro?',
                                        confirmText: 'Sí, {{ $item->estado ? 'desactivar' : 'activar' }}',
                                    }).then((ok) => { if (ok) $wire.delete({{ $item->id }}) })"
                                    class="{{ $item->estado ? 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300' : 'text-gray-400 dark:text-zinc-500' }} cursor-pointer">
                                    <i class="bi {{ $item->estado ? 'bi-trash' : 'bi-arrow-clockwise' }} text-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-4 text-center text-gray-500 dark:text-zinc-400">
                            No hay lotes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $lots->links() }}
    </div>

    <flux:modal wire:model="modalVisible" class="max-w-4xl">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $form->id ? 'Editar lote' : 'Nuevo lote' }}
            </flux:heading>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="md:col-span-2 xl:col-span-3">
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Buscar producto <span class="text-red-600">*</span></label>

                    @if ($selectedProductLabel)
                        <div class="flex items-center justify-between rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 dark:border-emerald-800 dark:bg-emerald-900/20">
                            <span class="text-sm text-emerald-700 dark:text-emerald-300">
                                {{ $selectedProductLabel }}
                            </span>
                            <button wire:click="clearProduct" type="button" class="text-red-500 hover:text-red-700">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    @else
                        <div class="relative">
                            <input
                                type="text"
                                wire:model.live="productSearch"
                                placeholder="Buscar por código, barras, nombre o genérico..."
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >

                            @if (!empty($productResults))
                                <div class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                                    @foreach ($productResults as $product)
                                        <button
                                            type="button"
                                            wire:click="selectProduct({{ $product['id'] }})"
                                            class="block w-full border-b border-gray-100 px-3 py-2 text-left text-sm hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-700"
                                        >
                                            {{ $product['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    @error('form.id_product')
                        <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <flux:input label="Lote" wire:model="form.codigo_lote" />
                @error('form.codigo_lote') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                <flux:input type="date" label="Fecha de vencimiento (Opcional)" wire:model="form.fecha_vencimiento" />
                @error('form.fecha_vencimiento') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                <flux:input label="Ubicación (Opcional)" wire:model="form.ubicacion" />
                @error('form.ubicacion') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                <flux:input type="number" step="0.01" label="Precio compra" wire:model="form.precio_compra" />
                @error('form.precio_compra') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                <flux:input type="number" step="0.01" label="Precio venta" wire:model="form.precio_venta" />
                @error('form.precio_venta') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                <flux:input type="number" label="Cantidad ingresada" wire:model="form.cantidad_ingresada" />
                @error('form.cantidad_ingresada') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                <flux:input type="number" label="Cantidad disponible" wire:model="form.cantidad_disponible" />
                @error('form.cantidad_disponible') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="danger" wire:click="cancel">
                    Cancelar
                </flux:button>

                <flux:button variant="primary" wire:click="save">
                    Guardar
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>
