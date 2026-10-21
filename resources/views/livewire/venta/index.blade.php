<div class="flex flex-col bg-zinc-200 p-4 dark:bg-zinc-950">
    @if (!$openCash)
        <div class="mb-3 flex flex-col gap-2 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300 md:flex-row md:items-center md:justify-between">
            <span><i class="bi bi-exclamation-triangle-fill mr-1"></i> Debes abrir tu caja primero para llevar el control de tus ventas.</span>
            <a href="{{ route('cash-registers') }}" wire:navigate
                class="shrink-0 rounded bg-red-600 px-3 py-1.5 text-center text-xs font-semibold text-white hover:bg-red-700">
                Abrir caja
            </a>
        </div>
    @elseif ($cashExpired)
        <div class="mb-3 flex flex-col gap-2 rounded border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300 md:flex-row md:items-center md:justify-between">
            <span>
                <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                Tienes una caja abierta desde el {{ $openCash->fecha_apertura?->format('d/m/Y') }} que ya venció su vigencia.
                Ciérrala o amplía su tiempo de vida para poder seguir vendiendo.
            </span>
            <div class="flex shrink-0 gap-2">
                <button type="button" wire:click="extendCash"
                    class="rounded bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">
                    Ampliar vigencia
                </button>
                <a href="{{ route('cash-registers') }}" wire:navigate
                    class="rounded border border-amber-400 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100 dark:text-amber-300 dark:hover:bg-amber-900/40">
                    Cerrar caja
                </a>
            </div>
        </div>
    @endif

    <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
        @can('Crear Ventas')
        <a href="{{ route('ventas.create') }}" wire:navigate
            class="flex items-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
            <i class="bi bi-bag-plus-fill text-primary"></i> Nueva venta
        </a>
        @endcan

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por fecha, cliente o método de pago..."
            class="w-full rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-primary focus:outline-none md:w-80 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500"
        >
    </div>

    <div class="overflow-auto rounded border border-zinc-300 dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Fecha</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Cliente</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Pago</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-right font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Total</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Estado</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Opciones</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($sales as $item)
                    <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ \Carbon\Carbon::parse($item->fecha_venta)->format('d/m/Y') }}
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            @if($item->client)
                                {{ $item->client?->person?->nombre }} {{ $item->client?->person?->apellido }}
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    CI: {{ $item->client?->person?->ci ?? '-' }}
                                </div>
                            @else
                                Consumidor final
                            @endif
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->metodo_pago }}
                        </td>

                        <td class="px-3 py-1.5 text-right text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->total, 2) }}
                        </td>

                        <td class="px-3 py-1.5 text-center">
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

                        <td class="px-3 py-1.5">
                            <div class="flex justify-center gap-3">
                                <a href="{{ route('ventas.view', $item->id) }}" wire:navigate
                                    class="cursor-pointer text-zinc-500 transition hover:text-primary dark:text-zinc-400">
                                    <i class="bi bi-eye text-base"></i>
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
                                            class="cursor-pointer text-zinc-500 transition hover:text-red-600 dark:text-zinc-400"
                                            title="Anular venta">
                                            <i class="bi bi-arrow-counterclockwise text-base"></i>
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
                        <td colspan="6" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">
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