<div class="p-6">
    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <h1 class="text-xl font-bold text-zinc-800 dark:text-zinc-100">
            Kardex
        </h1>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar producto, código o lote..."
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 md:w-96 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
        >
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left">FECHA</th>
                    <th class="px-4 py-3 text-left">PRODUCTO</th>
                    <th class="px-4 py-3 text-left">LOTE</th>
                    <th class="px-4 py-3 text-left">TIPO</th>
                    <th class="px-4 py-3 text-center">CANT.</th>
                    <th class="px-4 py-3 text-center">ANTES</th>
                    <th class="px-4 py-3 text-center">DESPUÉS</th>
                    <th class="px-4 py-3 text-left">REFERENCIA</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                @forelse($movements as $item)
                    <tr>
                        <td class="px-4 py-3">
                            {{ $item->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $item->product?->name }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $item->lot?->batch_code ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $item->movement_type }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            {{ $item->quantity }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            {{ $item->stock_before }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            {{ $item->stock_after }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $item->reference_type }} #{{ $item->reference_id }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-4 text-center text-zinc-500">
                            No hay movimientos de inventario.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $movements->links() }}
    </div>
</div>