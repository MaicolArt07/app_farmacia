<div class="p-6">
    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <a href="{{ route('purchases.create') }}" wire:navigate
            class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2 text-center text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600">
            <i class="bi bi-bag-plus mr-1"></i> Nueva compra
        </a>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por factura, fecha o proveedor..."
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none md:w-[28rem] dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-400"
        >
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">FECHA</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">PROVEEDOR</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">FACTURA</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">TOTAL</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">ESTADO</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">OPCIONES</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                @forelse ($purchases as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/70">
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ \Carbon\Carbon::parse($item->purchase_date)->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->supplier?->company_name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->invoice_number ?: '-' }}
                        </td>
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->total, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($item->status === 'CANCELLED')
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-m1edium text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                    Anulada
                                </span>
                            @else
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    Activa
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-center gap-3">
                                <a href="{{ route('purchases.view', $item->id) }}" wire:navigate
                                    class="cursor-pointer text-sky-500 transition hover:text-sky-600 dark:text-sky-400">
                                    <i class="bi bi-eye text-lg"></i>
                                </a>

                                <a href="{{ route('purchases.edit', $item->id) }}" wire:navigate
                                    class="cursor-pointer text-yellow-500 transition hover:text-yellow-600 dark:text-yellow-400">
                                    <i class="bi bi-pencil-square text-lg"></i>
                                </a>

                                <button wire:click="delete({{ $item->id }})"
                                    class="{{ $item->state ? 'text-red-600 hover:text-red-800 dark:text-red-400' : 'text-gray-400 dark:text-zinc-500' }} cursor-pointer">
                                    <i class="bi {{ $item->state ? 'bi-trash' : 'bi-arrow-clockwise' }} text-lg"></i>
                                </button>

                                @can('Cambiar Estado Compras')
                                    @if($item->status !== 'CANCELLED')
                                        <button
                                            wire:click="cancelPurchase({{ $item->id }})"
                                            wire:confirm="¿Está segura de anular esta compra? Se revertirá el stock ingresado."
                                            class="cursor-pointer text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                            title="Anular compra">
                                            <i class="bi bi-arrow-counterclockwise text-lg"></i>
                                        </button>
                                    @else
                                        <span class="text-xs font-semibold text-red-500">
                                            Anulada
                                        </span>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500 dark:text-zinc-400">
                            No hay compras registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $purchases->links() }}
    </div>
</div>