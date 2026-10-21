<div class="bg-zinc-200 p-4 dark:bg-zinc-950">
    <div class="mb-3 flex flex-col gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900 md:flex-row md:items-center md:justify-between">
        <h1 class="text-xl font-bold text-zinc-800 dark:text-zinc-100">
            Kardex
        </h1>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar producto, código o lote..."
            class="w-full rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 focus:border-primary focus:outline-none md:w-96 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        >
    </div>

    <div class="overflow-auto rounded border border-zinc-300 dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">FECHA</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">PRODUCTO</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">LOTE</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">TIPO</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">CANT.</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">ANTES</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">DESPUÉS</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">REFERENCIA</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
                @forelse($movements as $item)
                    <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->product?->name }}
                        </td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->lot?->codigo_lote ?? '-' }}
                        </td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->tipo_movimiento }}
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
                            {{ $item->reference_type }} #{{ $item->reference_id }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            No hay movimientos de inventario.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $movements->links() }}
    </div>
</div>