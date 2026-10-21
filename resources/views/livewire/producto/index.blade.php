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

    <div class="overflow-auto rounded border border-zinc-300 dark:border-zinc-700">
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
                    <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
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
                        <td colspan="8" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">
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
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Laboratorio <span class="text-xs font-normal text-zinc-400 dark:text-zinc-500">(Opcional)</span></label>
                    <x-search-select
                        model="form.id_laboratory"
                        :value="$form->id_laboratory"
                        :options="$laboratories->map(fn ($l) => ['value' => $l->id, 'label' => $l->nombre])->values()"
                    />
                    @error('form.id_laboratory') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Código de Producto SIN <span class="text-xs font-normal text-zinc-400 dark:text-zinc-500">(Opcional, necesario para facturación)</span></label>
                    <x-search-select
                        model="form.id_sin_product_code"
                        :value="$form->id_sin_product_code"
                        :options="$sinProductCodes->map(fn ($p) => ['value' => $p->id, 'label' => $p->codigo_producto . ' - ' . $p->descripcion])->values()"
                        placeholder="Buscar producto homologado por el SIN..."
                    />
                    <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Catálogo oficial del SIN para tu actividad económica (farmacia). Obligatorio antes de poder facturar este producto.</p>
                    @error('form.id_sin_product_code') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
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
                        <button type="button" wire:click="nextStep" class="rounded border border-primary bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:brightness-110 cursor-pointer dark:border-primary dark:bg-primary dark:text-white dark:hover:brightness-110">
                            Siguiente <i class="bi bi-arrow-right text-primary"></i>
                        </button>
                    @elseif($isCreating && $step === 2)
                        <button type="button" wire:click="skipStock" class="rounded border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20 cursor-pointer">
                            Omitir por ahora
                        </button>
                        <button type="button" wire:click="save" class="rounded border border-primary bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:brightness-110 cursor-pointer dark:border-primary dark:bg-primary dark:text-white dark:hover:brightness-110">
                            <i class="bi bi-check-lg text-white"></i> Guardar
                        </button>
                    @else
                        <button type="button" wire:click="save" class="rounded border border-primary bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:brightness-110 cursor-pointer dark:border-primary dark:bg-primary dark:text-white dark:hover:brightness-110">
                            <i class="bi bi-check-lg text-white"></i> Guardar
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </flux:modal>
</div>
