<div class="mx-auto max-w-5xl p-6">
    <div class="mb-6">
        <a href="{{ route('products') }}" wire:navigate class="mb-2 inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
            <i class="bi bi-arrow-left"></i> Volver a productos
        </a>
        <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
            {{ $form->id ? 'Editar producto' : 'Nuevo producto' }}
        </h1>
    </div>

    @if($isCreating)
        <div class="mb-4 flex items-center gap-3 text-sm">
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

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

        @if($step === 1)
        <div class="grid grid-cols-1 gap-4 px-6 py-5 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Código <span class="text-red-600">*</span></label>
                <input type="text" wire:model="form.code"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.code') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Código de barras <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="text" wire:model="form.barcode"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.barcode') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="xl:col-span-2">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Nombre comercial <span class="text-red-600">*</span></label>
                <input type="text" wire:model="form.name"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="xl:col-span-2">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Nombre genérico <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="text" wire:model="form.generic_name"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.generic_name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Concentración <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="text" wire:model="form.concentration"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.concentration') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Precio venta <span class="text-red-600">*</span></label>
                <input type="number" step="0.01" wire:model="form.sale_price"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.sale_price') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Stock mínimo <span class="text-red-600">*</span></label>
                <input type="number" wire:model="form.minimum_stock"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.minimum_stock') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Categoría <span class="text-red-600">*</span></label>
                <x-search-select
                    model="form.id_category"
                    :value="$form->id_category"
                    :options="$categories->map(fn ($c) => ['value' => $c->id, 'label' => $c->name])->values()"
                />
                @error('form.id_category') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Presentación <span class="text-red-600">*</span></label>
                <x-search-select
                    model="form.id_presentation"
                    :value="$form->id_presentation"
                    :options="$presentations->map(fn ($p) => ['value' => $p->id, 'label' => $p->name])->values()"
                />
                @error('form.id_presentation') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Laboratorio <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <x-search-select
                    model="form.id_laboratory"
                    :value="$form->id_laboratory"
                    :options="$laboratories->map(fn ($l) => ['value' => $l->id, 'label' => $l->name])->values()"
                />
                @error('form.id_laboratory') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2 xl:col-span-4">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Descripción <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <textarea wire:model="form.description" rows="3"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                @error('form.description') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2 xl:col-span-4">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    <input type="checkbox" wire:model="form.requires_prescription"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800">
                    Requiere receta médica
                </label>
            </div>
        </div>
        @else
        <div class="grid grid-cols-1 gap-4 px-6 py-5 md:grid-cols-2">
            <div class="md:col-span-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                <i class="bi bi-info-circle mr-1"></i>
                Este paso es opcional. Si registras el stock inicial aquí, se creará automáticamente el primer lote de este producto (igual que si lo hicieras desde Compras o Ajustes de Inventario).
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Código de lote <span class="text-red-600">*</span></label>
                <input type="text" wire:model="initial_batch_code"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('initial_batch_code') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Fecha de vencimiento <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="date" wire:model="initial_expiration_date"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('initial_expiration_date') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Precio de compra <span class="text-red-600">*</span></label>
                <input type="number" step="0.01" min="0" wire:model="initial_purchase_price"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('initial_purchase_price') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Cantidad inicial <span class="text-red-600">*</span></label>
                <input type="number" min="1" wire:model="initial_quantity"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('initial_quantity') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>
        </div>
        @endif

        <div class="flex items-center justify-between gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
            <div>
                @if($step === 2)
                    <button wire:click="prevStep"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-zinc-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                        <i class="bi bi-arrow-left"></i> Atrás
                    </button>
                @endif
            </div>

            <div class="flex gap-2">
                <button wire:click="cancel"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-zinc-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                    Cancelar
                </button>

                @if($isCreating && $step === 1)
                    <button wire:click="nextStep"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                        Siguiente <i class="bi bi-arrow-right"></i>
                    </button>
                @elseif($isCreating && $step === 2)
                    <button wire:click="skipStock"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-zinc-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                        Omitir por ahora
                    </button>
                    <button wire:click="save"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                        Guardar
                    </button>
                @else
                    <button wire:click="save"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                        Guardar
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
