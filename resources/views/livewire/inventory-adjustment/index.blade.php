<div class="p-6">

    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        @can('Crear Ajustes Inventario')
            <a href="{{ route('inventory-adjustments.create') }}" wire:navigate
                class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2 text-center text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600">
                <i class="bi bi-sliders2-vertical mr-1"></i> Nuevo ajuste
            </a>
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
                            {{ $item->product?->name ?? '-' }}
                            <div class="text-xs text-gray-500 dark:text-zinc-400">
                                {{ $item->product?->code ?? '-' }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->lot?->batch_code ?? '-' }}
                            <div class="text-xs text-gray-500 dark:text-zinc-400">
                                Vence:
                                {{ $item->lot?->expiration_date ? $item->lot->expiration_date->format('d/m/Y') : '-' }}
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
                            {{ $item->reason }}
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


</div>