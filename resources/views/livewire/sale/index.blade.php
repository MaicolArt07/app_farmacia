<div class="p-6">
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
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">FACTURA</th>
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

                        <td class="px-4 py-3 text-center">
                            @if ($item->invoice)
                                @php
                                    $invoiceBadgeClasses = match ($item->invoice->estado) {
                                        'ENVIADA' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                        'OBSERVADA' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                        'ERROR' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                        'ANULADA' => 'bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
                                        default => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                    };
                                @endphp
                                <span class="rounded-full px-3 py-1 text-xs font-medium {{ $invoiceBadgeClasses }}">
                                    {{ $item->invoice->estado_label }}
                                </span>
                                @if ($item->invoice->isSimulado())
                                    <div class="mt-1 text-[10px] uppercase text-amber-600 dark:text-amber-400">Simulada</div>
                                @endif
                            @else
                                <span class="text-xs text-gray-400 dark:text-zinc-500">Sin facturar</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex justify-center gap-3">
                                <a href="{{ route('sales.view', $item->id) }}" wire:navigate
                                    class="cursor-pointer text-sky-500 transition hover:text-sky-600 dark:text-sky-400">
                                    <i class="bi bi-eye text-lg"></i>
                                </a>

                                @can('Crear Facturas')
                                    @if($item->status !== 'CANCELLED' && (!$item->invoice || $item->invoice->estado !== 'ENVIADA'))
                                        <button
                                            wire:click="generarFactura({{ $item->id }})"
                                            class="cursor-pointer text-indigo-600 transition hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            title="Generar factura">
                                            <i class="bi bi-receipt text-lg"></i>
                                        </button>
                                    @endif
                                @endcan

                                @can('Cambiar Estado Ventas')
                                    @if($item->status !== 'CANCELLED')
                                        <button
                                            wire:click="cancelSale({{ $item->id }})"
                                            wire:confirm="¿Está segura de anular esta venta? Se devolverá el stock y se ajustará caja."
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
                        <td colspan="7" class="px-4 py-4 text-center text-gray-500 dark:text-zinc-400">
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