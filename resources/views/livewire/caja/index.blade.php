<div class="bg-zinc-200 p-4 dark:bg-zinc-950">

    <div class="mb-3 flex flex-col gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900 md:flex-row md:items-center md:justify-between">
        @can('Crear Caja')
            <button type="button" wire:click="openOpenModal"
                class="flex items-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                <i class="bi bi-cash-coin text-primary"></i> Abrir caja
            </button>
        @endcan

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por fecha, usuario o estado..."
            class="w-full rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-primary focus:outline-none md:w-[28rem] dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500"
        >
    </div>

    <div class="overflow-auto rounded border border-zinc-300 dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">INICIO</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">USUARIO</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-right font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">INICIAL</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-right font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">ESPERADO</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-right font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">CONTADO</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-right font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">DIF.</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">ESTADO</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">FIN</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">OPCIONES</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($cashRegisters as $item)
                    <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ \Carbon\Carbon::parse($item->fecha_apertura)->format('d/m/Y') }}
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->user?->name ?? '-' }}
                        </td>

                        <td class="px-3 py-1.5 text-right text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->monto_apertura, 2) }}
                        </td>

                        <td class="px-3 py-1.5 text-right text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->monto_esperado, 2) }}
                        </td>

                        <td class="px-3 py-1.5 text-right text-zinc-800 dark:text-zinc-100">
                            {{ $item->monto_cierre !== null ? number_format($item->monto_cierre, 2) : '-' }}
                        </td>

                        <td class="px-3 py-1.5 text-right">
                            @if($item->diferencia === null)
                                <span class="text-zinc-500 dark:text-zinc-400">-</span>
                            @else
                                <span class="{{ $item->diferencia == 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ number_format($item->diferencia, 2) }}
                                </span>
                            @endif
                        </td>

                        <td class="px-3 py-1.5 text-center">
                            @if($item->status === 'OPEN')
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    Abierta
                                </span>
                            @else
                                <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    Cerrada
                                </span>
                            @endif
                        </td>

                        <td class="px-3 py-1.5 text-center">
                            @if($item->status === 'OPEN')
                                @if($item->expires_at)
                                    <div class="text-xs text-zinc-600 dark:text-zinc-300">
                                        {{ $item->expires_at->format('d/m/Y H:i') }}
                                    </div>
                                    @if($item->isExpired())
                                        <span class="mt-1 inline-block rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                            Vencida
                                        </span>
                                    @else
                                        <span class="mt-1 inline-block rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                            Vigente
                                        </span>
                                    @endif
                                @else
                                    <span class="text-zinc-500 dark:text-zinc-400">-</span>
                                @endif
                            @else
                                <span class="text-zinc-500 dark:text-zinc-400">-</span>
                            @endif
                        </td>

                        <td class="px-3 py-1.5">
                            <div class="flex justify-center gap-3">

                                <button wire:click="openDetailModal({{ $item->id }})"
                                    class="cursor-pointer text-sky-600 hover:text-sky-800 dark:text-sky-400"
                                    title="Ver detalle">
                                    <i class="bi bi-eye text-lg"></i>
                                </button>

                                @if($item->status === 'OPEN')
                                    @can('Crear Movimientos Caja')
                                        <button wire:click="openMovementModal({{ $item->id }})"
                                            class="cursor-pointer text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                            title="Registrar movimiento">
                                            <i class="bi bi-plus-circle text-lg"></i>
                                        </button>
                                    @endcan

                                    @can('Editar Caja')
                                        <button wire:click="openExtendModal({{ $item->id }})"
                                            class="cursor-pointer text-indigo-600 hover:text-indigo-800 dark:text-indigo-400"
                                            title="Ampliar vigencia">
                                            <i class="bi bi-hourglass-split text-lg"></i>
                                        </button>

                                        <button wire:click="openCloseCashModal({{ $item->id }})"
                                            class="cursor-pointer text-amber-600 hover:text-amber-800 dark:text-amber-400"
                                            title="Cerrar caja">
                                            <i class="bi bi-lock-fill text-lg"></i>
                                        </button>
                                    @endcan
                                @endif

                                @can('Cambiar Estado Caja')
                                    <button type="button"
                                        x-on:click.prevent="confirmAction({
                                            text: '¿Deseas {{ $item->estado ? 'desactivar' : 'activar' }} este registro?',
                                            confirmText: 'Sí, {{ $item->estado ? 'desactivar' : 'activar' }}',
                                        }).then((ok) => { if (ok) $wire.delete({{ $item->id }}) })"
                                        class="{{ $item->estado ? 'text-red-600 hover:text-red-800 dark:text-red-400' : 'text-zinc-400 dark:text-zinc-500' }} cursor-pointer"
                                        title="Activar / Desactivar">
                                        <i class="bi {{ $item->estado ? 'bi-trash' : 'bi-arrow-clockwise' }} text-lg"></i>
                                    </button>
                                @endcan

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            No hay cajas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $cashRegisters->links() }}
    </div>

    @if($openModalVisible)
        @include('livewire.caja.modal.open')
    @endif

    @if($movementModalVisible)
        @include('livewire.caja.modal.movement')
    @endif

    @if($closeModalVisible)
        @include('livewire.caja.modal.close')
    @endif

    @if($extendModalVisible)
        @include('livewire.caja.modal.extend')
    @endif

    @if($detailModalVisible)
        @include('livewire.caja.modal.detail')
    @endif

</div>
