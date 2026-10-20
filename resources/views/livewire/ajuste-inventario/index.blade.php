<div class="p-6">

    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        @can('Crear Ajustes Inventario')
            <button type="button" wire:click="create"
                class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2 text-center text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600">
                <i class="bi bi-sliders2-vertical mr-1"></i> Nuevo ajuste
            </button>
        @endcan

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por producto, código o lote..."
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none md:w-[28rem] dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-400"
        >
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">FECHA</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">PRODUCTO</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">LOTE</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">TIPO</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">CANT.</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">ANTES</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">DESPUÉS</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">USUARIO</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">MOTIVO</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                @forelse ($adjustments as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/70">
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->created_at?->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->product?->nombre ?? '-' }}
                            <div class="text-xs text-gray-500 dark:text-zinc-400">
                                {{ $item->product?->code ?? '-' }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->lot?->codigo_lote ?? '-' }}
                            <div class="text-xs text-gray-500 dark:text-zinc-400">
                                Vence:
                                {{ $item->lot?->fecha_vencimiento ? $item->lot->fecha_vencimiento->format('d/m/Y') : '-' }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($item->type === 'INCREASE')
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    Aumento
                                </span>
                            @else
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                    Disminución
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center text-zinc-800 dark:text-zinc-100">
                            {{ $item->quantity }}
                        </td>

                        <td class="px-4 py-3 text-center text-zinc-800 dark:text-zinc-100">
                            {{ $item->stock_before }}
                        </td>

                        <td class="px-4 py-3 text-center text-zinc-800 dark:text-zinc-100">
                            {{ $item->stock_after }}
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->user?->name ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->motivo }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-4 text-center text-gray-500 dark:text-zinc-400">
                            No hay ajustes de inventario registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $adjustments->links() }}
    </div>

    <flux:modal wire:model="modalVisible" class="max-w-3xl">
        <div class="space-y-6">
            <flux:heading size="lg">
                Nuevo ajuste de inventario
            </flux:heading>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

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

                    @error('form.id_product')
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
                                @disabled(!$form->id_product)
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

                    @error('form.id_lot')
                        <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <flux:select label="Tipo de ajuste" wire:model="form.type">
                    <flux:select.option value="INCREASE">Aumentar stock</flux:select.option>
                    <flux:select.option value="DECREASE">Disminuir stock</flux:select.option>
                </flux:select>
                @error('form.type')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror

                <flux:input type="number" min="1" label="Cantidad" wire:model="form.quantity" />
                @error('form.quantity')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror

                <flux:textarea
                    label="Motivo"
                    wire:model="form.reason"
                    rows="3"
                    placeholder="Ej.: error de conteo físico, producto dañado, ajuste por inventario..."
                    class="md:col-span-2"
                />
                @error('form.reason')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="danger" wire:click="cancel">
                    Cancelar
                </flux:button>

                <flux:button variant="primary" wire:click="save">
                    Guardar ajuste
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>