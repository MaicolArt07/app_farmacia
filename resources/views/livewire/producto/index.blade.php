<div class="p-6">
    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <button type="button" wire:click="create"
            class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2 text-center text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600">
            <i class="bi bi-capsule-pill mr-1"></i> Nuevo producto
        </button>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por código, barra, nombre, genérico, categoría..."
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none md:w-[28rem] dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-400"
        >
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">CÓDIGO</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">PRODUCTO</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">CATEGORÍA</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">PRESENTACIÓN</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">PRECIO</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">RECETA</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">ESTADO</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">OPCIONES</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                @forelse ($products as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/70">
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->code }}
                            <div class="text-xs text-gray-500 dark:text-zinc-400">
                                {{ $item->barcode ?: 'Sin código de barras' }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            <div class="font-medium">{{ $item->nombre }}</div>
                            <div class="text-xs text-gray-500 dark:text-zinc-400">
                                {{ $item->nombre_generico ?: '-' }}
                                @if($item->concentracion)
                                    | {{ $item->concentracion }}
                                @endif
                            </div>
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->category?->nombre ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->presentation?->nombre ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->precio_venta, 2) }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $item->requiere_receta ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300' }}">
                                {{ $item->requiere_receta ? 'Sí' : 'No' }}
                            </span>
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
                        <td colspan="8" class="px-4 py-4 text-center text-gray-500 dark:text-zinc-400">
                            No hay productos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
                    <div class="flex items-center gap-2 {{ $step === 1 ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full {{ $step === 1 ? 'bg-blue-600 text-white' : 'bg-emerald-600 text-white' }}">
                            @if($step > 1) <i class="bi bi-check-lg text-xs"></i> @else 1 @endif
                        </span>
                        Datos del producto
                    </div>
                    <div class="h-px flex-1 bg-gray-200 dark:bg-zinc-700"></div>
                    <div class="flex items-center gap-2 {{ $step === 2 ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-zinc-400 dark:text-zinc-500' }}">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full {{ $step === 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-zinc-500 dark:bg-zinc-700' }}">2</span>
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
                        model="form.id_category"
                        :value="$form->id_category"
                        :options="$categories->map(fn ($c) => ['value' => $c->id, 'label' => $c->nombre])->values()"
                    />
                    @error('form.id_category') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Presentación <span class="text-red-600">*</span></label>
                    <x-search-select
                        model="form.id_presentation"
                        :value="$form->id_presentation"
                        :options="$presentations->map(fn ($p) => ['value' => $p->id, 'label' => $p->nombre])->values()"
                    />
                    @error('form.id_presentation') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Laboratorio <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                    <x-search-select
                        model="form.id_laboratory"
                        :value="$form->id_laboratory"
                        :options="$laboratories->map(fn ($l) => ['value' => $l->id, 'label' => $l->nombre])->values()"
                    />
                    @error('form.id_laboratory') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Código de Producto SIN <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional, necesario para facturación)</span></label>
                    <x-search-select
                        model="form.id_sin_product_code"
                        :value="$form->id_sin_product_code"
                        :options="$sinProductCodes->map(fn ($p) => ['value' => $p->id, 'label' => $p->codigo_producto . ' - ' . $p->descripcion])->values()"
                        placeholder="Buscar producto homologado por el SIN..."
                    />
                    <p class="mt-1 text-xs text-gray-400 dark:text-zinc-500">Catálogo oficial del SIN para tu actividad económica (farmacia). Obligatorio antes de poder facturar este producto.</p>
                    @error('form.id_sin_product_code') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <flux:textarea label="Descripción (Opcional)" wire:model="form.descripcion" rows="3" />
                    @error('form.descripcion') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                        <input type="checkbox" wire:model="form.requiere_receta"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800">
                        Requiere receta médica
                    </label>
                </div>
            </div>
            @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                    <i class="bi bi-info-circle mr-1"></i>
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

            <div class="flex items-center justify-between gap-2">
                <div>
                    @if($step === 2)
                        <flux:button variant="ghost" wire:click="prevStep">
                            <i class="bi bi-arrow-left"></i> Atrás
                        </flux:button>
                    @endif
                </div>

                <div class="flex gap-2">
                    <flux:button variant="danger" wire:click="cancel">
                        Cancelar
                    </flux:button>

                    @if($isCreating && $step === 1)
                        <flux:button variant="primary" wire:click="nextStep">
                            Siguiente <i class="bi bi-arrow-right"></i>
                        </flux:button>
                    @elseif($isCreating && $step === 2)
                        <flux:button variant="ghost" wire:click="skipStock">
                            Omitir por ahora
                        </flux:button>
                        <flux:button variant="primary" wire:click="save">
                            Guardar
                        </flux:button>
                    @else
                        <flux:button variant="primary" wire:click="save">
                            Guardar
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    </flux:modal>
</div>
