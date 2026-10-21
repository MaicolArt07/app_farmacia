<div class="flex flex-col bg-zinc-200 p-4 dark:bg-zinc-950">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
        <button type="button" wire:click="create"
            class="flex items-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
            <i class="bi bi-box2-heart text-primary"></i> Nuevo lote
        </button>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por lote, producto, código, vencimiento..."
            class="w-full rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-primary focus:outline-none md:w-[28rem] dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500"
        >
    </div>

    <div class="overflow-auto rounded border border-zinc-300 dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Producto</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Lote</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Vencimiento</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">P. Compra</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">P. Venta</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Ingreso</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Disponible</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Ubicación</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Estado</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Opciones</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($lots as $item)
                    <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->product?->nombre ?? '-' }}
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $item->product?->code ?? '-' }}
                            </div>
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->codigo_lote }}
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->fecha_vencimiento ? $item->fecha_vencimiento->format('d/m/Y') : 'Sin vencimiento' }}
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->precio_compra, 2) }}
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->precio_venta, 2) }}
                        </td>

                        <td class="px-3 py-1.5 text-center text-zinc-800 dark:text-zinc-100">
                            {{ $item->cantidad_ingresada }}
                        </td>

                        <td class="px-3 py-1.5 text-center">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $item->cantidad_disponible > 0 ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $item->cantidad_disponible }}
                            </span>
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->ubicacion ?: '-' }}
                        </td>

                        <td class="px-3 py-1.5 text-center">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $item->estado ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $item->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>

                        <td class="px-3 py-1.5">
                            <div class="flex justify-center gap-3">
                                <button type="button" wire:click="edit({{ $item->id }})"
                                    class="cursor-pointer text-zinc-500 transition hover:text-primary dark:text-zinc-400">
                                    <i class="bi bi-pencil-square text-base"></i>
                                </button>

                                <button
                                    type="button"
                                    x-on:click.prevent="confirmAction({
                                        text: '¿Deseas {{ $item->estado ? 'desactivar' : 'activar' }} este registro?',
                                        confirmText: 'Sí, {{ $item->estado ? 'desactivar' : 'activar' }}',
                                    }).then((ok) => { if (ok) $wire.delete({{ $item->id }}) })"
                                    class="cursor-pointer text-zinc-500 transition hover:text-red-600 dark:text-zinc-400">
                                    <i class="bi {{ $item->estado ? 'bi-trash' : 'bi-arrow-clockwise' }} text-base"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">
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

    <div x-data="{ open: false }" x-init="$watch('$wire.modalVisible', value => open = value)" x-show="$wire.modalVisible" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-zinc-900/30" wire:click="cancel"></div>

        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="relative flex w-full max-w-4xl flex-col rounded border border-zinc-300 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">

            <div class="flex items-center justify-between border-b border-zinc-300 bg-zinc-200 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ $form->id ? 'Editar lote' : 'Nuevo lote' }}
                </h2>
                <button wire:click="cancel" class="text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 gap-4 px-4 py-5 md:grid-cols-2 xl:grid-cols-3">
                <div class="space-y-1 md:col-span-2 xl:col-span-3">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Buscar producto <span class="text-red-600">*</span></label>

                    @if ($selectedProductLabel)
                        <div class="flex items-center justify-between rounded border border-zinc-400 bg-zinc-100 px-3 py-2 dark:border-zinc-600 dark:bg-zinc-800">
                            <span class="text-sm text-zinc-700 dark:text-zinc-200">
                                {{ $selectedProductLabel }}
                            </span>
                            <button wire:click="clearProduct" type="button" class="cursor-pointer text-zinc-500 hover:text-red-600 dark:text-zinc-400">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    @else
                        <div class="relative">
                            <input
                                type="text"
                                wire:model.live="productSearch"
                                placeholder="Buscar por código, barras, nombre o genérico..."
                                class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                            >

                            @if (!empty($productResults))
                                <div class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded border border-zinc-300 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                                    @foreach ($productResults as $product)
                                        <button
                                            type="button"
                                            wire:click="selectProduct({{ $product['id'] }})"
                                            class="block w-full cursor-pointer border-b border-zinc-200 px-3 py-2 text-left text-sm text-zinc-800 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-700"
                                        >
                                            {{ $product['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    @error('form.id_product')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Lote</label>
                    <input type="text" wire:model="form.codigo_lote"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.codigo_lote') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Fecha de vencimiento (Opcional)</label>
                    <input type="date" wire:model="form.fecha_vencimiento"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.fecha_vencimiento') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Ubicación (Opcional)</label>
                    <input type="text" wire:model="form.ubicacion"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.ubicacion') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Precio compra</label>
                    <input type="number" step="0.01" wire:model="form.precio_compra"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.precio_compra') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Precio venta</label>
                    <input type="number" step="0.01" wire:model="form.precio_venta"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.precio_venta') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Cantidad ingresada</label>
                    <input type="number" wire:model="form.cantidad_ingresada"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.cantidad_ingresada') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Cantidad disponible</label>
                    <input type="number" wire:model="form.cantidad_disponible"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.cantidad_disponible') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-zinc-300 bg-zinc-100 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <button wire:click="cancel" class="rounded border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20 cursor-pointer">
                    Cancelar
                </button>
                <button wire:click="save" class="rounded border border-primary bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:brightness-110 cursor-pointer dark:border-primary dark:bg-primary dark:text-white dark:hover:brightness-110">
                    <i class="bi bi-check-lg text-white"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
