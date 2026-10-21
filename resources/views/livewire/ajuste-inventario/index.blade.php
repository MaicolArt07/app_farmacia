<div class="flex flex-col bg-zinc-200 p-4 dark:bg-zinc-950">

    <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
        @can('Crear Ajustes Inventario')
            <button type="button" wire:click="create"
                class="flex items-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                <i class="bi bi-sliders2-vertical text-primary"></i> Nuevo ajuste
            </button>
        @endcan

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por producto, código o lote..."
            class="w-full rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-primary focus:outline-none md:w-80 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500"
        >
    </div>

    <div class="overflow-auto rounded border border-zinc-300 dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Fecha</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Producto</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Lote</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Tipo</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Cant.</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Antes</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Después</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Usuario</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Motivo</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($adjustments as $item)
                    <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->created_at?->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->product?->nombre ?? '-' }}
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $item->product?->code ?? '-' }}
                            </div>
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->lot?->codigo_lote ?? '-' }}
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                Vence:
                                {{ $item->lot?->fecha_vencimiento ? $item->lot->fecha_vencimiento->format('d/m/Y') : '-' }}
                            </div>
                        </td>

                        <td class="px-3 py-1.5 text-center">
                            @if($item->type === 'INCREASE')
                                <span class="rounded-full px-3 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    Aumento
                                </span>
                            @else
                                <span class="rounded-full px-3 py-1 text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                    Disminución
                                </span>
                            @endif
                        </td>

                        <td class="px-3 py-1.5 text-center text-zinc-800 dark:text-zinc-100">
                            {{ $item->quantity }}
                        </td>

                        <td class="px-3 py-1.5 text-center text-zinc-800 dark:text-zinc-100">
                            {{ $item->stock_before }}
                        </td>

                        <td class="px-3 py-1.5 text-center text-zinc-800 dark:text-zinc-100">
                            {{ $item->stock_after }}
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->user?->name ?? '-' }}
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->motivo }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">
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
                        <div class="flex items-center justify-between rounded border border-zinc-400 bg-white px-3 py-2 dark:border-zinc-600 dark:bg-zinc-800">
                            <span class="text-sm text-zinc-800 dark:text-zinc-100">
                                {{ $selectedProductLabel }}
                            </span>

                            <button wire:click="resetProduct" type="button" class="cursor-pointer text-zinc-500 hover:text-red-600 dark:text-zinc-400">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    @else
                        <div class="relative">
                            <input
                                type="text"
                                wire:model.live="productSearch"
                                placeholder="Buscar producto por código, nombre o genérico..."
                                class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                            >

                            @if(!empty($productResults))
                                <div class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded border border-zinc-300 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                                    @foreach($productResults as $product)
                                        <button
                                            type="button"
                                            wire:click="selectProduct({{ $product['id'] }})"
                                            class="block w-full cursor-pointer border-b border-zinc-200 px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-700"
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
                        <div class="flex items-center justify-between rounded border border-zinc-400 bg-white px-3 py-2 dark:border-zinc-600 dark:bg-zinc-800">
                            <span class="text-sm text-zinc-800 dark:text-zinc-100">
                                {{ $selectedLotLabel }}
                            </span>

                            <button wire:click="clearLot" type="button" class="cursor-pointer text-zinc-500 hover:text-red-600 dark:text-zinc-400">
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
                                class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 disabled:bg-zinc-100 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:disabled:bg-zinc-700"
                            >

                            @if(!empty($lotResults))
                                <div class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded border border-zinc-300 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                                    @foreach($lotResults as $lot)
                                        <button
                                            type="button"
                                            wire:click="selectLot({{ $lot['id'] }})"
                                            class="block w-full cursor-pointer border-b border-zinc-200 px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-700"
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

            <div class="flex justify-end gap-3 border-t border-zinc-300 pt-4 dark:border-zinc-700">
                <button wire:click="cancel" type="button" class="rounded border border-zinc-400 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 cursor-pointer">
                    Cancelar
                </button>

                <button wire:click="save" type="button" class="rounded border border-zinc-400 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700">
                    <i class="bi bi-check-lg text-primary"></i> Guardar ajuste
                </button>
            </div>
        </div>
    </flux:modal>

</div>
