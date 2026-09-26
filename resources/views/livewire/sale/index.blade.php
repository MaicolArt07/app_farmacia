<div class="p-6">
    @if (!$openCash)
        <div class="mb-4 flex flex-col gap-2 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300 md:flex-row md:items-center md:justify-between">
            <span><i class="bi bi-exclamation-triangle-fill mr-1"></i> Debes abrir tu caja primero para llevar el control de tus ventas.</span>
            <a href="{{ route('cash-registers') }}" wire:navigate
                class="shrink-0 rounded-lg bg-red-600 px-3 py-1.5 text-center text-xs font-semibold text-white hover:bg-red-700">
                Abrir caja
            </a>
        </div>
    @elseif ($cashExpired)
        <div class="mb-4 flex flex-col gap-2 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300 md:flex-row md:items-center md:justify-between">
            <span>
                <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                Tienes una caja abierta desde el {{ $openCash->opening_date?->format('d/m/Y') }} que ya venció su vigencia.
                Ciérrala o amplía su tiempo de vida para poder seguir vendiendo.
            </span>
            <div class="flex shrink-0 gap-2">
                <button type="button" wire:click="extendCash"
                    class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">
                    Ampliar vigencia
                </button>
                <a href="{{ route('cash-registers') }}" wire:navigate
                    class="rounded-lg border border-amber-400 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100 dark:text-amber-300 dark:hover:bg-amber-900/40">
                    Cerrar caja
                </a>
            </div>
        </div>
    @endif

    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        @can('Crear Ventas')
        <a href="{{ route('sales.create') }}" wire:navigate
            class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2 text-center text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600">
            <i class="bi bi-bag-plus-fill mr-1"></i> Nueva venta
        </a>
        @endcan

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por fecha, cliente o método de pago..."
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none md:w-[28rem] dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-400"
        >
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">FECHA</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">CLIENTE</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">PAGO</th>
                    <th class="px-4 py-3 text-right text-zinc-700 dark:text-zinc-200">TOTAL</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">ESTADO</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">OPCIONES</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                @forelse ($sales as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/70">
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ \Carbon\Carbon::parse($item->sale_date)->format('d/m/Y') }}
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            @if($item->client)
                                {{ $item->client?->person?->name }} {{ $item->client?->person?->lastname }}
                                <div class="text-xs text-gray-500 dark:text-zinc-400">
                                    CI: {{ $item->client?->person?->ci ?? '-' }}
                                </div>
                            @else
                                Consumidor final
                            @endif
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->payment_method }}
                        </td>

                        <td class="px-4 py-3 text-right text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->total, 2) }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($item->status === 'CANCELLED')
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">
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
                                <a href="{{ route('sales.view', $item->id) }}" wire:navigate
                                    class="cursor-pointer text-sky-500 transition hover:text-sky-600 dark:text-sky-400">
                                    <i class="bi bi-eye text-lg"></i>
                                </a>

                                @can('Cambiar Estado Ventas')
                                    @if($item->status !== 'CANCELLED')
                                        <button
                                            type="button"
                                            x-on:click.prevent="confirmAction({
                                                title: '¿Anular esta venta?',
                                                text: 'Se devolverá el stock y se ajustará la caja.',
                                                confirmText: 'Sí, anular',
                                            }).then((ok) => { if (ok) $wire.cancelSale({{ $item->id }}) })"
                                            class="cursor-pointer text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                            title="Anular venta">
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
                            No hay ventas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $sales->links() }}
    </div>
</div>