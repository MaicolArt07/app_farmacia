<div class="flex flex-col bg-zinc-200 p-4 dark:bg-zinc-950">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
        <button type="button" wire:click="create"
            class="flex items-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
            <i class="bi bi-capsule-pill text-primary"></i> Nuevo producto
        </button>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por código, barra, nombre, genérico, categoría..."
            class="w-full rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-primary focus:outline-none md:w-80 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500"
        >
    </div>

    {{-- Tabla (md en adelante) --}}
    <div class="hidden overflow-auto rounded border border-zinc-300 md:block dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Código</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Producto</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Categoría</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Presentación</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Precio</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Receta</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Estado</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Opciones</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($products as $item)
                    <tr wire:click="view({{ $item->id }})" class="cursor-pointer border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->code }}
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $item->barcode ?: 'Sin código de barras' }}
                            </div>
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            <div class="font-medium">{{ $item->nombre }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $item->nombre_generico ?: '-' }}
                                @if($item->concentracion)
                                    | {{ $item->concentracion }}
                                @endif
                            </div>
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->category?->nombre ?? '-' }}
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->presentation?->nombre ?? '-' }}
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->precio_venta, 2) }}
                        </td>

                        <td class="px-3 py-1.5 text-center">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $item->requiere_receta ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300' }}">
                                {{ $item->requiere_receta ? 'Sí' : 'No' }}
                            </span>
                        </td>

                        <td class="px-3 py-1.5 text-center">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $item->estado ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $item->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>

                        <td class="px-3 py-1.5" onclick="event.stopPropagation()">
                            <div class="flex justify-center gap-3">
                                <button type="button" wire:click="edit({{ $item->id }})"
                                    class="cursor-pointer text-zinc-500 transition hover:text-primary dark:text-zinc-400">
                                    <i class="bi bi-pencil-square text-base"></i>
                                </button>

                                @if($item->id_producto_unidad)
                                    <button type="button" title="Abrir paquete ({{ $item->unidades_por_paquete }} unidades sueltas)"
                                        x-on:click.prevent="confirmAction({
                                            title: '¿Abrir 1 paquete?',
                                            text: 'Se descuenta 1 paquete y se agregan {{ $item->unidades_por_paquete }} unidades sueltas.',
                                            confirmText: 'Sí, abrir',
                                            danger: false,
                                        }).then((ok) => { if (ok) $wire.openPackage({{ $item->id }}) })"
                                        class="cursor-pointer text-amber-600 transition hover:text-amber-800 dark:text-amber-400">
                                        <i class="bi bi-box-seam text-base"></i>
                                    </button>
                                @endif

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
                        <td colspan="8" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            No hay productos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tarjetas responsive (solo en móvil) --}}
    <div class="flex flex-col gap-2 md:hidden">
        @forelse ($products as $item)
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
                        <p class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $item->nombre }}</p>
                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $item->code }} &middot; {{ number_format($item->precio_venta, 2) }}</p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3" onclick="event.stopPropagation()">
                        <button type="button" wire:click="edit({{ $item->id }})"
                            class="cursor-pointer text-zinc-500 transition hover:text-primary dark:text-zinc-400">
                            <i class="bi bi-pencil-square text-base"></i>
                        </button>

                        @if($item->id_producto_unidad)
                            <button type="button" title="Abrir paquete"
                                x-on:click.prevent="confirmAction({
                                    title: '¿Abrir 1 paquete?',
                                    text: 'Se descuenta 1 paquete y se agregan {{ $item->unidades_por_paquete }} unidades sueltas.',
                                    confirmText: 'Sí, abrir',
                                    danger: false,
                                }).then((ok) => { if (ok) $wire.openPackage({{ $item->id }}) })"
                                class="cursor-pointer text-amber-600 transition hover:text-amber-800 dark:text-amber-400">
                                <i class="bi bi-box-seam text-base"></i>
                            </button>
                        @endif

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
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Categoría</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->category?->nombre ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Presentación</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->presentation?->nombre ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Receta</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->requiere_receta ? 'Sí' : 'No' }}</p>
                    </div>
                    <div>
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
                No hay productos registrados.
            </div>
        @endforelse
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $products->links() }}
    </div>

    <flux:modal wire:model="modalVisible" class="max-w-5xl">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $form->id ? 'Editar producto' : 'Nuevo producto' }}
            </flux:heading>

            @if($isCreating)
                <div class="flex items-center gap-3 text-sm">
                    <div class="flex items-center gap-2 {{ $step === 1 ? 'font-semibold text-zinc-800 dark:text-zinc-100' : 'text-emerald-600 dark:text-emerald-400' }}">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full border border-zinc-400 {{ $step === 1 ? 'bg-white text-zinc-700 dark:bg-zinc-800 dark:text-zinc-100' : 'bg-emerald-600 text-white border-emerald-600' }}">
                            @if($step > 1) <i class="bi bi-check-lg text-xs"></i> @else 1 @endif
                        </span>
                        Datos del producto
                    </div>
                    <div class="h-px flex-1 bg-zinc-300 dark:bg-zinc-700"></div>
                    <div class="flex items-center gap-2 {{ $step === 2 ? 'font-semibold text-zinc-800 dark:text-zinc-100' : 'text-zinc-400 dark:text-zinc-500' }}">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full border border-zinc-400 {{ $step === 2 ? 'bg-white text-zinc-700 dark:bg-zinc-800 dark:text-zinc-100' : 'bg-zinc-200 text-zinc-500 dark:bg-zinc-700' }}">2</span>
                        Stock inicial <span class="text-xs font-normal">(Opcional)</span>
                    </div>
                </div>
            @endif

            @if($step === 1)
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <flux:input label="Código" wire:model="form.code" />
                @error('form.code') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                <flux:input label="Código de barras (Opcional)" wire:model="form.barcode" />
                @error('form.barcode') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                <div class="xl:col-span-2">
                    <flux:input label="Nombre comercial" wire:model="form.nombre" />
                    @error('form.nombre') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="xl:col-span-2">
                    <flux:input label="Nombre genérico (Opcional)" wire:model="form.nombre_generico" />
                    @error('form.nombre_generico') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <flux:input label="Concentración (Opcional)" wire:model="form.concentracion" />
                @error('form.concentracion') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                <flux:input type="number" step="0.01" label="Precio venta" wire:model="form.precio_venta" />
                @error('form.precio_venta') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                <flux:input type="number" label="Stock mínimo" wire:model="form.stock_minimo" />
                @error('form.stock_minimo') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Categoría <span class="text-red-600">*</span></label>
                    <x-search-select
                        model="form.id_categoria"
                        :value="$form->id_categoria"
                        :options="$categories->map(fn ($c) => ['value' => $c->id, 'label' => $c->nombre])->values()"
                    />
                    @error('form.id_categoria') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Presentación <span class="text-red-600">*</span></label>
                    <x-search-select
                        model="form.id_presentacion"
                        :value="$form->id_presentacion"
                        :options="$presentations->map(fn ($p) => ['value' => $p->id, 'label' => $p->nombre])->values()"
                    />
                    @error('form.id_presentacion') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Laboratorio <span class="text-xs font-normal text-zinc-400 dark:text-zinc-500">(Opcional)</span></label>
                    <x-search-select
                        model="form.id_laboratorio"
                        :value="$form->id_laboratorio"
                        :options="$laboratories->map(fn ($l) => ['value' => $l->id, 'label' => $l->nombre])->values()"
                    />
                    @error('form.id_laboratorio') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Código de Producto SIN <span class="text-xs font-normal text-zinc-400 dark:text-zinc-500">(Opcional, necesario para facturación)</span></label>
                    <x-search-select
                        model="form.id_codigo_producto_sin"
                        :value="$form->id_codigo_producto_sin"
                        :options="$sinProductCodes->map(fn ($p) => ['value' => $p->id, 'label' => $p->codigo_producto . ' - ' . $p->descripcion])->values()"
                        placeholder="Buscar producto homologado por el SIN..."
                    />
                    <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Catálogo oficial del SIN para tu actividad económica (farmacia). Obligatorio antes de poder facturar este producto.</p>
                    @error('form.id_codigo_producto_sin') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Paquete que también se vende suelto (ej. Huggies x20 -> Huggies unidad) --}}
                <div class="md:col-span-2 xl:col-span-3">
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Se abre en (producto unidad) <span class="text-xs font-normal text-zinc-400 dark:text-zinc-500">(Opcional, solo si es un paquete que también se vende suelto)</span></label>
                    <x-search-select
                        model="form.id_producto_unidad"
                        :value="$form->id_producto_unidad"
                        :options="$unitOptions"
                        placeholder="Buscar el producto unidad..."
                    />
                    @error('form.id_producto_unidad') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <flux:input type="number" min="2" label="Unidades por paquete" wire:model="form.unidades_por_paquete" />
                    @error('form.unidades_por_paquete') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <flux:textarea label="Descripción (Opcional)" wire:model="form.descripcion" rows="3" />
                    @error('form.descripcion') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                        <input type="checkbox" wire:model="form.requiere_receta"
                            class="rounded border-zinc-400 accent-primary dark:border-zinc-600">
                        Requiere receta médica
                    </label>
                </div>
            </div>
            @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2 rounded border border-zinc-300 bg-zinc-100 px-4 py-3 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    <i class="bi bi-info-circle text-primary mr-1"></i>
                    Este paso es opcional. Si registras el stock inicial aquí, se creará automáticamente el primer lote de este producto (igual que si lo hicieras desde Compras o Ajustes de Inventario).
                </div>

                <flux:input label="Código de lote" wire:model="stockForm.codigo_lote" />
                @error('stockForm.codigo_lote') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                <flux:input type="date" label="Fecha de vencimiento (Opcional)" wire:model="stockForm.fecha_vencimiento" />
                @error('stockForm.fecha_vencimiento') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                <flux:input type="number" step="0.01" min="0" label="Precio de compra" wire:model="stockForm.precio_compra" />
                @error('stockForm.precio_compra') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                <flux:input type="number" min="1" label="Cantidad inicial" wire:model="stockForm.cantidad" />
                @error('stockForm.cantidad') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>
            @endif

            <div class="flex items-center justify-between gap-2 border-t border-zinc-300 pt-4 dark:border-zinc-700">
                <div>
                    @if($step === 2)
                        <button type="button" wire:click="prevStep" class="rounded border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20 cursor-pointer">
                            <i class="bi bi-arrow-left"></i> Atrás
                        </button>
                    @endif
                </div>

                <div class="flex gap-2">
                    <button type="button" wire:click="cancel" class="rounded border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20 cursor-pointer">
                        Cancelar
                    </button>

                    @if($isCreating && $step === 1)
                        <button type="button" wire:click="nextStep" class="rounded border border-primary bg-white px-4 py-2 text-sm font-semibold text-primary shadow-sm transition hover:bg-primary/10 cursor-pointer dark:bg-zinc-800 dark:hover:bg-primary/20">
                            Siguiente <i class="bi bi-arrow-right text-primary"></i>
                        </button>
                    @elseif($isCreating && $step === 2)
                        <button type="button" wire:click="skipStock" class="rounded border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20 cursor-pointer">
                            Omitir por ahora
                        </button>
                        <button type="button" wire:click="save" class="rounded border border-primary bg-white px-4 py-2 text-sm font-semibold text-primary shadow-sm transition hover:bg-primary/10 cursor-pointer dark:bg-zinc-800 dark:hover:bg-primary/20">
                            <i class="bi bi-check-lg text-primary"></i> Guardar
                        </button>
                    @else
                        <button type="button" wire:click="save" class="rounded border border-primary bg-white px-4 py-2 text-sm font-semibold text-primary shadow-sm transition hover:bg-primary/10 cursor-pointer dark:bg-zinc-800 dark:hover:bg-primary/20">
                            <i class="bi bi-check-lg text-primary"></i> Guardar
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </flux:modal>

    <div x-data="{ open: false }" x-init="$watch('$wire.viewingId', value => open = !!value)" x-show="!!$wire.viewingId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-zinc-900/30" wire:click="closeView"></div>

        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="relative flex w-full max-w-2xl flex-col rounded border border-zinc-300 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900 max-h-[90vh]">

            @if ($viewing)
                <div class="flex items-center justify-between border-b border-zinc-300 bg-zinc-200 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Detalle del producto</h2>
                    <button wire:click="closeView" class="text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="space-y-4 overflow-y-auto px-4 py-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Código</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->code }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Código de barras</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->barcode ?: '-' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Nombre comercial</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->nombre }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Nombre genérico</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->nombre_generico ?: '-' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Concentración</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->concentracion ?: '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Requiere receta</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->requiere_receta ? 'Sí' : 'No' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Categoría</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->category?->nombre ?? '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Presentación</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->presentation?->nombre ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Laboratorio</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->laboratory?->nombre ?? '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Código de Producto SIN</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->sinProductCode ? $viewing->sinProductCode->codigo_producto . ' - ' . $viewing->sinProductCode->descripcion : '-' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Precio de venta</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ number_format($viewing->precio_venta, 2) }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Stock mínimo</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->stock_minimo }}</p>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Descripción</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100 whitespace-pre-line">{{ $viewing->descripcion ?: '-' }}</p>
                    </div>

                    <div class="space-y-1">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Estado</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->estado ? 'Activo' : 'Inactivo' }}</p>
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
</div>
