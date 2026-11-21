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

    <div class="hidden overflow-auto rounded border border-zinc-300 md:block dark:border-zinc-700">
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
                    <tr wire:click="view({{ $item->id }})" class="cursor-pointer border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->product?->nombre_completo ?? '-' }}
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

                        <td class="px-3 py-1.5" onclick="event.stopPropagation()">
                            <div class="flex justify-center gap-3">
                                <button type="button" wire:click="openCuadre({{ $item->id }})"
                                    class="cursor-pointer text-sky-600 transition hover:text-sky-800 dark:text-sky-400"
                                    title="Cuadre: cuánto entró, cuánto se vendió y cuánto debe haber">
                                    <i class="bi bi-clipboard-data text-base"></i>
                                </button>

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

    <div class="flex flex-col gap-2 md:hidden">
        @forelse ($lots as $item)
            <div
                x-data="{ open: false }"
                wire:click="view({{ $item->id }})"
                class="cursor-pointer rounded border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div class="flex items-center gap-2">
                    <button type="button" x-on:click.stop="open = !open"
                        class="flex h-6 w-6 shrink-0 cursor-pointer items-center justify-center rounded-full border border-primary text-primary transition hover:bg-primary hover:text-white">
                        <i class="bi text-xs" :class="open ? 'bi-dash' : 'bi-plus'"></i>
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $item->product?->nombre_completo ?? '-' }}</p>
                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">Lote: {{ $item->codigo_lote }}</p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3" onclick="event.stopPropagation()">
                        <button type="button" wire:click="openCuadre({{ $item->id }})"
                            class="cursor-pointer text-sky-600 dark:text-sky-400" title="Cuadre del lote">
                            <i class="bi bi-clipboard-data text-base"></i>
                        </button>

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
                </div>

                <div x-show="open" x-collapse x-cloak onclick="event.stopPropagation()" class="mt-2 grid grid-cols-2 gap-2 border-t border-zinc-200 pt-2 pl-8 dark:border-zinc-800">
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Vencimiento</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->fecha_vencimiento ? $item->fecha_vencimiento->format('d/m/Y') : 'Sin vencimiento' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Ubicación</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->ubicacion ?: '-' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">P. Compra</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ number_format($item->precio_compra, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">P. Venta</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ number_format($item->precio_venta, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Ingreso</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->cantidad_ingresada }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Disponible</span>
                        <p>
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $item->cantidad_disponible > 0 ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $item->cantidad_disponible }}
                            </span>
                        </p>
                    </div>
                    <div class="col-span-2">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Estado</span>
                        <p>
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $item->estado ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $item->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded border border-zinc-300 bg-white px-3 py-4 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                No hay lotes registrados.
            </div>
        @endforelse
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

                    @error('form.id_producto')
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
                <button wire:click="save" class="rounded border border-primary bg-white px-4 py-2 text-sm font-semibold text-primary shadow-sm transition hover:bg-primary/10 cursor-pointer dark:bg-zinc-800 dark:hover:bg-primary/20">
                    <i class="bi bi-check-lg text-primary"></i> Guardar
                </button>
            </div>
        </div>
    </div>

    {{-- Modal de detalle (solo lectura) --}}
    <div x-data="{ open: false }" x-init="$watch('$wire.viewingId', value => open = !!value)" x-show="!!$wire.viewingId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-zinc-900/30" wire:click="closeView"></div>

        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="relative flex w-full max-w-lg flex-col rounded border border-zinc-300 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">

            @if ($viewing)
                <div class="flex items-center justify-between border-b border-zinc-300 bg-zinc-200 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Detalle de lote</h2>
                    <button wire:click="closeView" class="text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="space-y-4 px-4 py-5">
                    <div class="space-y-1">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Producto</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->product?->nombre_completo ?? '-' }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Código producto</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->product?->code ?? '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Lote</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->codigo_lote }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Vencimiento</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->fecha_vencimiento ? $viewing->fecha_vencimiento->format('d/m/Y') : 'Sin vencimiento' }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Ubicación</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->ubicacion ?: '-' }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Precio compra</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ number_format($viewing->precio_compra, 2) }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Precio venta</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ number_format($viewing->precio_venta, 2) }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Cantidad ingresada</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->cantidad_ingresada }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Cantidad disponible</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->cantidad_disponible }}</p>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Estado</span>
                        <p>
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $viewing->estado ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $viewing->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Creado</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Actualizado</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->updated_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-zinc-300 bg-zinc-100 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <button wire:click="closeView" class="rounded border border-zinc-400 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 cursor-pointer">
                        Cerrar
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- ================= Cuadre del lote ================= --}}
    @if($cuadre)
        @php $c = $cuadre; $ok = $c['diferencia'] === 0; @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-zinc-800">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                            <i class="bi bi-clipboard-data mr-1 text-sky-600"></i>Cuadre del lote
                        </h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $c['lot']->product?->nombre_completo }} · {{ $c['lot']->codigo_lote }}</p>
                    </div>
                    <button wire:click="closeCuadre" class="text-gray-500 hover:text-red-500 dark:text-zinc-400"><i class="bi bi-x-lg"></i></button>
                </div>

                <div class="max-h-[70vh] space-y-4 overflow-y-auto px-6 py-5">
                    {{-- Cuenta --}}
                    <div class="rounded-lg border border-gray-200 p-3 text-sm dark:border-zinc-700">
                        <div class="flex justify-between text-zinc-700 dark:text-zinc-300"><span><i class="bi bi-box-arrow-in-down text-emerald-600"></i> Entró (carga inicial / ingresos)</span><span class="font-semibold">+{{ $c['entradas'] }}</span></div>
                        <div class="flex justify-between text-zinc-700 dark:text-zinc-300"><span><i class="bi bi-cart-check text-red-600"></i> Vendido ({{ $c['num_ventas'] }} {{ $c['num_ventas'] === 1 ? 'venta' : 'ventas' }})</span><span class="font-semibold">−{{ $c['vendidas'] }}</span></div>
                        @if($c['devueltas'])
                            <div class="flex justify-between text-zinc-700 dark:text-zinc-300"><span><i class="bi bi-arrow-return-left text-amber-600"></i> Volvió por cancelaciones / devoluciones</span><span class="font-semibold">+{{ $c['devueltas'] }}</span></div>
                        @endif
                        @if($c['ajuste_mas'])
                            <div class="flex justify-between text-zinc-700 dark:text-zinc-300"><span><i class="bi bi-plus-circle text-sky-600"></i> Ajustes de inventario (+)</span><span class="font-semibold">+{{ $c['ajuste_mas'] }}</span></div>
                        @endif
                        @if($c['ajuste_menos'])
                            <div class="flex justify-between text-zinc-700 dark:text-zinc-300"><span><i class="bi bi-dash-circle text-sky-600"></i> Ajustes de inventario (−)</span><span class="font-semibold">−{{ $c['ajuste_menos'] }}</span></div>
                        @endif
                        @if($c['otros'])
                            <div class="flex justify-between text-zinc-700 dark:text-zinc-300"><span>Otros (apertura de paquetes, etc.)</span><span class="font-semibold">{{ $c['otros'] > 0 ? '+' : '' }}{{ $c['otros'] }}</span></div>
                        @endif
                        <div class="mt-1 flex justify-between border-t border-gray-200 pt-1 font-bold text-zinc-800 dark:border-zinc-700 dark:text-zinc-100"><span>= Debería haber</span><span>{{ $c['calculado'] }}</span></div>
                        <div class="flex justify-between font-bold text-zinc-800 dark:text-zinc-100"><span>Disponible en el sistema</span><span>{{ $c['disponible'] }}</span></div>
                    </div>

                    @if($ok)
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
                            <i class="bi bi-check-circle-fill"></i> Cuadra: todo lo que entró y salió de este lote está registrado.
                        </div>
                    @else
                        <div class="rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong>No cuadra por {{ $c['diferencia'] > 0 ? '+' : '' }}{{ $c['diferencia'] }}.</strong>
                            El stock se cambió sin dejar registro (por ejemplo, editando el lote a mano).
                            Para corregirlo con registro use <strong>Ajustes de inventario</strong>.
                        </div>
                    @endif

                    {{-- Historial --}}
                    <div>
                        <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Historial del lote</div>
                        <div class="max-h-64 overflow-y-auto rounded-lg border border-gray-200 dark:border-zinc-700">
                            <table class="min-w-full text-sm">
                                <thead class="sticky top-0 bg-gray-100 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-3 py-1.5 text-left text-xs text-zinc-600 dark:text-zinc-300">FECHA</th>
                                        <th class="px-3 py-1.5 text-left text-xs text-zinc-600 dark:text-zinc-300">MOVIMIENTO</th>
                                        <th class="px-3 py-1.5 text-left text-xs text-zinc-600 dark:text-zinc-300">USUARIO</th>
                                        <th class="px-3 py-1.5 text-right text-xs text-zinc-600 dark:text-zinc-300">CANT.</th>
                                        <th class="px-3 py-1.5 text-right text-xs text-zinc-600 dark:text-zinc-300">QUEDA</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                                    @php $running = 0; @endphp
                                    @forelse($c['moves'] as $m)
                                        @php $running += $m->delta; @endphp
                                        <tr class="bg-white dark:bg-zinc-900" title="{{ $m->descripcion }}">
                                            <td class="px-3 py-1.5 text-zinc-700 dark:text-zinc-300" style="white-space: nowrap">{{ $m->fecha?->format('d/m/Y H:i') }}</td>
                                            <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $m->label }}</td>
                                            <td class="px-3 py-1.5 text-xs text-zinc-500 dark:text-zinc-400">{{ $m->user }}</td>
                                            <td class="px-3 py-1.5 text-right font-semibold {{ $m->delta < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $m->delta > 0 ? '+' : '' }}{{ $m->delta }}</td>
                                            <td class="px-3 py-1.5 text-right text-zinc-700 dark:text-zinc-300">{{ $running }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-3 py-3 text-center text-xs text-zinc-500">Este lote no tiene movimientos registrados.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
                    <button wire:click="closeCuadre"
                        class="cursor-pointer rounded-lg border border-gray-300 px-4 py-2 text-zinc-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
