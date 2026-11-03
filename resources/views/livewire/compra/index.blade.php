<div class="flex flex-col bg-zinc-200 p-4 dark:bg-zinc-950">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
        <a href="{{ route('compras.create') }}" wire:navigate
            class="flex items-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
            <i class="bi bi-bag-plus text-primary"></i> Nueva compra
        </a>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por factura, fecha o proveedor..."
            class="w-full rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-primary focus:outline-none md:w-80 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500"
        >
    </div>

    <div class="hidden overflow-auto rounded border border-zinc-300 md:block dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Fecha</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Proveedor</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Factura</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Total</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Estado</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Opciones</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($purchases as $item)
                    <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ \Carbon\Carbon::parse($item->fecha_compra)->format('d/m/Y') }}
                        </td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->supplier?->razon_social ?? '-' }}
                        </td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->numero_factura ?: '-' }}
                        </td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
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
                                <a href="{{ route('compras.view', $item->id) }}" wire:navigate
                                    class="cursor-pointer text-zinc-500 transition hover:text-primary dark:text-zinc-400">
                                    <i class="bi bi-eye text-base"></i>
                                </a>

                                <a href="{{ route('compras.edit', $item->id) }}" wire:navigate
                                    class="cursor-pointer text-zinc-500 transition hover:text-primary dark:text-zinc-400">
                                    <i class="bi bi-pencil-square text-base"></i>
                                </a>

                                <button type="button"
                                    x-on:click.prevent="confirmAction({
                                        text: '¿Deseas {{ $item->estado ? 'desactivar' : 'activar' }} este registro?',
                                        confirmText: 'Sí, {{ $item->estado ? 'desactivar' : 'activar' }}',
                                    }).then((ok) => { if (ok) $wire.delete({{ $item->id }}) })"
                                    class="{{ $item->estado ? 'text-zinc-500 hover:text-red-600 dark:text-zinc-400' : 'text-zinc-400 dark:text-zinc-600' }} cursor-pointer transition">
                                    <i class="bi {{ $item->estado ? 'bi-trash' : 'bi-arrow-clockwise' }} text-base"></i>
                                </button>

                                @can('Cambiar Estado Compras')
                                    @if($item->status !== 'CANCELLED')
                                        <button
                                            type="button"
                                            x-on:click.prevent="confirmAction({
                                                title: '¿Anular esta compra?',
                                                text: 'Se revertirá el stock ingresado.',
                                                confirmText: 'Sí, anular',
                                            }).then((ok) => { if (ok) $wire.cancelPurchase({{ $item->id }}) })"
                                            class="cursor-pointer text-zinc-500 transition hover:text-red-600 dark:text-zinc-400"
                                            title="Anular compra">
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
                            No hay compras registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex flex-col gap-2 md:hidden">
        @forelse ($purchases as $item)
            <div x-data="{ open: false }" class="rounded border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <button type="button" x-on:click.stop="open = !open"
                        class="flex h-6 w-6 shrink-0 cursor-pointer items-center justify-center rounded-full border border-primary text-primary transition hover:bg-primary hover:text-white">
                        <i class="bi text-xs" :class="open ? 'bi-dash' : 'bi-plus'"></i>
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $item->supplier?->razon_social ?? '-' }}</p>
                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ \Carbon\Carbon::parse($item->fecha_compra)->format('d/m/Y') }} &middot; {{ number_format($item->total, 2) }}</p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3" onclick="event.stopPropagation()">
                        <a href="{{ route('compras.view', $item->id) }}" wire:navigate
                            class="cursor-pointer text-zinc-500 transition hover:text-primary dark:text-zinc-400">
                            <i class="bi bi-eye text-base"></i>
                        </a>

                        <a href="{{ route('compras.edit', $item->id) }}" wire:navigate
                            class="cursor-pointer text-zinc-500 transition hover:text-primary dark:text-zinc-400">
                            <i class="bi bi-pencil-square text-base"></i>
                        </a>

                        <button type="button"
                            x-on:click.prevent="confirmAction({
                                text: '¿Deseas {{ $item->estado ? 'desactivar' : 'activar' }} este registro?',
                                confirmText: 'Sí, {{ $item->estado ? 'desactivar' : 'activar' }}',
                            }).then((ok) => { if (ok) $wire.delete({{ $item->id }}) })"
                            class="{{ $item->estado ? 'text-zinc-500 hover:text-red-600 dark:text-zinc-400' : 'text-zinc-400 dark:text-zinc-600' }} cursor-pointer transition">
                            <i class="bi {{ $item->estado ? 'bi-trash' : 'bi-arrow-clockwise' }} text-base"></i>
                        </button>

                        @can('Cambiar Estado Compras')
                            @if($item->status !== 'CANCELLED')
                                <button
                                    type="button"
                                    x-on:click.prevent="confirmAction({
                                        title: '¿Anular esta compra?',
                                        text: 'Se revertirá el stock ingresado.',
                                        confirmText: 'Sí, anular',
                                    }).then((ok) => { if (ok) $wire.cancelPurchase({{ $item->id }}) })"
                                    class="cursor-pointer text-zinc-500 transition hover:text-red-600 dark:text-zinc-400"
                                    title="Anular compra">
                                    <i class="bi bi-arrow-counterclockwise text-base"></i>
                                </button>
                            @endif
                        @endcan
                    </div>
                </div>

                <div x-show="open" x-collapse x-cloak onclick="event.stopPropagation()" class="mt-2 grid grid-cols-2 gap-2 border-t border-zinc-200 pt-2 pl-8 dark:border-zinc-800">
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Factura</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->numero_factura ?: '-' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Estado</span>
                        <p>
                            @if($item->status === 'CANCELLED')
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">Anulada</span>
                            @else
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Activa</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded border border-zinc-300 bg-white px-3 py-4 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                No hay compras registradas.
            </div>
        @endforelse
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $purchases->links() }}
    </div>
</div>
