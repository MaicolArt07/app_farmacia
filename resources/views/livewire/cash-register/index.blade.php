<div class="p-6">

    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        @can('Crear Caja')
            <a href="{{ route('cash-registers.create') }}" wire:navigate
                class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2 text-center text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600">
                <i class="bi bi-cash-coin mr-1"></i> Abrir caja
            </a>
        @endcan

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por fecha, usuario o estado..."
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none md:w-[28rem] dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-400"
        >
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">FECHA</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">USUARIO</th>
                    <th class="px-4 py-3 text-right text-zinc-700 dark:text-zinc-200">INICIAL</th>
                    <th class="px-4 py-3 text-right text-zinc-700 dark:text-zinc-200">ESPERADO</th>
                    <th class="px-4 py-3 text-right text-zinc-700 dark:text-zinc-200">CONTADO</th>
                    <th class="px-4 py-3 text-right text-zinc-700 dark:text-zinc-200">DIF.</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">ESTADO</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">OPCIONES</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                @forelse ($cashRegisters as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/70">
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ \Carbon\Carbon::parse($item->opening_date)->format('d/m/Y') }}
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->user?->name ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-right text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->opening_amount, 2) }}
                        </td>

                        <td class="px-4 py-3 text-right text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->expected_amount, 2) }}
                        </td>

                        <td class="px-4 py-3 text-right text-zinc-800 dark:text-zinc-100">
                            {{ $item->closing_amount !== null ? number_format($item->closing_amount, 2) : '-' }}
                        </td>

                        <td class="px-4 py-3 text-right">
                            @if($item->difference === null)
                                <span class="text-zinc-500 dark:text-zinc-400">-</span>
                            @else
                                <span class="{{ $item->difference == 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ number_format($item->difference, 2) }}
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
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

                        <td class="px-4 py-3">
                            <div class="flex justify-center gap-3">

                                @if($item->status === 'OPEN')
                                    @can('Crear Movimientos Caja')
                                        <button wire:click="openMovementModal({{ $item->id }})"
                                            class="cursor-pointer text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                            title="Registrar movimiento">
                                            <i class="bi bi-plus-circle text-lg"></i>
                                        </button>
                                    @endcan

                                    @can('Editar Caja')
                                        <button wire:click="openCloseCashModal({{ $item->id }})"
                                            class="cursor-pointer text-amber-600 hover:text-amber-800 dark:text-amber-400"
                                            title="Cerrar caja">
                                            <i class="bi bi-lock-fill text-lg"></i>
                                        </button>
                                    @endcan
                                @endif

                                @can('Cambiar Estado Caja')
                                    <button wire:click="delete({{ $item->id }})"
                                        class="{{ $item->state ? 'text-red-600 hover:text-red-800 dark:text-red-400' : 'text-gray-400 dark:text-zinc-500' }} cursor-pointer"
                                        title="Activar / Desactivar">
                                        <i class="bi {{ $item->state ? 'bi-trash' : 'bi-arrow-clockwise' }} text-lg"></i>
                                    </button>
                                @endcan

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-4 text-center text-gray-500 dark:text-zinc-400">
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

    @if($movementModalVisible)
        @include('livewire.cash-register.modal.movement')
    @endif

    @if($closeModalVisible)
        @include('livewire.cash-register.modal.close')
    @endif

</div>