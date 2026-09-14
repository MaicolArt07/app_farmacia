<div class="mx-auto max-w-3xl p-6">
    <div class="mb-6">
        <a href="{{ route('inventory-adjustments') }}" wire:navigate class="mb-2 inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
            <i class="bi bi-arrow-left"></i> Volver a ajustes de inventario
        </a>
        <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
            Nuevo ajuste de inventario
        </h1>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid grid-cols-1 gap-4 px-6 py-5 md:grid-cols-2">

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Producto <span class="text-red-600">*</span></label>

                @if($selectedProductLabel)
                    <div class="flex items-center justify-between rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 dark:border-emerald-800 dark:bg-emerald-900/20">
                        <span class="text-sm text-emerald-700 dark:text-emerald-300">
                            {{ $selectedProductLabel }}
                        </span>

                        <button wire:click="resetProduct" type="button" class="text-red-500 hover:text-red-700">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                @else
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live="productSearch"
                            placeholder="Buscar producto por código, nombre o genérico..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >

                        @if(!empty($productResults))
                            <div class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                                @foreach($productResults as $product)
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

                @error('id_product')
                    <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Lote <span class="text-red-600">*</span></label>

                @if($selectedLotLabel)
                    <div class="flex items-center justify-between rounded-lg border border-sky-300 bg-sky-50 px-3 py-2 dark:border-sky-800 dark:bg-sky-900/20">
                        <span class="text-sm text-sky-700 dark:text-sky-300">
                            {{ $selectedLotLabel }}
                        </span>

                        <button wire:click="clearLot" type="button" class="text-red-500 hover:text-red-700">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                @else
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live="lotSearch"
                            placeholder="Buscar lote por código o vencimiento..."
                            @disabled(!$id_product)
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 disabled:bg-gray-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:disabled:bg-zinc-700"
                        >

                        @if(!empty($lotResults))
                            <div class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                                @foreach($lotResults as $lot)
                                    <button
                                        type="button"
                                        wire:click="selectLot({{ $lot['id'] }})"
                                        class="block w-full border-b border-gray-100 px-3 py-2 text-left text-sm hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-700"
                                    >
                                        {{ $lot['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                @error('id_lot')
                    <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Tipo de ajuste <span class="text-red-600">*</span></label>
                <select wire:model="type"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="INCREASE">Aumentar stock</option>
                    <option value="DECREASE">Disminuir stock</option>
                </select>

                @error('type')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Cantidad <span class="text-red-600">*</span></label>
                <input
                    type="number"
                    min="1"
                    wire:model="quantity"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-right dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                >

                @error('quantity')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Motivo <span class="text-red-600">*</span></label>
                <textarea
                    wire:model="reason"
                    rows="3"
                    placeholder="Ej.: error de conteo físico, producto dañado, ajuste por inventario..."
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                ></textarea>

                @error('reason')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

        </div>

        <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
            <button wire:click="cancel"
                class="rounded-lg border border-gray-300 px-4 py-2 text-zinc-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                Cancelar
            </button>

            <button wire:click="save"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                Guardar ajuste
            </button>
        </div>
    </div>
</div>
