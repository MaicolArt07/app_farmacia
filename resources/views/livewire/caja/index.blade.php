<div class="flex flex-col bg-zinc-200 p-4 dark:bg-zinc-950">

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

    {{-- Colores de las columnas de ventas (lo más importante de la tabla) --}}
    <style>
        .col-ef  { background: #ecfdf5; color: #047857; }   /* Efectivo: verde */
        .col-qr  { background: #f0f9ff; color: #0369a1; }   /* QR: celeste */
        .col-tot { background: #fffbeb; color: #b45309; }   /* Total: ámbar */
        th.col-ef  { background: #a7f3d0; color: #065f46; }
        th.col-qr  { background: #bae6fd; color: #075985; }
        th.col-tot { background: #fde68a; color: #92400e; }
        tfoot .col-ef  { background: #a7f3d0; color: #065f46; }
        tfoot .col-qr  { background: #bae6fd; color: #075985; }
        tfoot .col-tot { background: #fde68a; color: #92400e; }
        .dark .col-ef  { background: rgba(16,185,129,.12); color: #6ee7b7; }
        .dark .col-qr  { background: rgba(14,165,233,.12); color: #7dd3fc; }
        .dark .col-tot { background: rgba(245,158,11,.12); color: #fcd34d; }
        .dark th.col-ef, .dark tfoot .col-ef   { background: rgba(16,185,129,.30); color: #a7f3d0; }
        .dark th.col-qr, .dark tfoot .col-qr   { background: rgba(14,165,233,.30); color: #bae6fd; }
        .dark th.col-tot, .dark tfoot .col-tot { background: rgba(245,158,11,.30); color: #fde68a; }
    </style>

    {{-- Tabla (md en adelante) --}}
    <div class="hidden overflow-auto rounded border border-zinc-300 md:block dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">INICIO</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">USUARIO</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-right font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">INICIAL</th>
                    <th class="col-ef border-b border-zinc-400 px-3 py-1.5 text-right font-semibold dark:border-zinc-600"><i class="bi bi-cash-stack"></i> EFECTIVO</th>
                    <th class="col-qr border-b border-zinc-400 px-3 py-1.5 text-right font-semibold dark:border-zinc-600"><i class="bi bi-qr-code"></i> QR</th>
                    <th class="col-tot border-b border-zinc-400 px-3 py-1.5 text-right font-semibold dark:border-zinc-600">TOTAL</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-right font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">CONTADO</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-right font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">DIF.</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">FIN</th>
                    @role('admin')
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">OBS.</th>
                    @endrole
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">OPCIONES</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($cashRegisters as $item)
                    <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ \Carbon\Carbon::parse($item->fecha_apertura)->format('d/m/Y') }}
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->created_at?->format('H:i') }}</span>
                        </td>

                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->user?->name ?? '-' }}
                        </td>

                        <td class="px-3 py-1.5 text-right text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->monto_apertura, 2) }}
                        </td>

                        {{-- Ventas de la caja: efectivo, QR y total --}}
                        <td class="col-ef px-3 py-1.5 text-right font-medium">
                            {{ number_format((float) $item->ventas_efectivo, 2) }}
                        </td>

                        <td class="col-qr px-3 py-1.5 text-right font-medium">
                            {{ number_format((float) $item->ventas_qr, 2) }}
                        </td>

                        <td class="col-tot px-3 py-1.5 text-right font-bold">
                            {{ number_format((float) $item->ventas_efectivo + (float) $item->ventas_qr, 2) }}
                        </td>

                        <td class="px-3 py-1.5 text-right text-zinc-800 dark:text-zinc-100">
                            {{ $item->monto_cierre !== null ? number_format($item->monto_cierre, 2) : '-' }}
                        </td>

                        <td class="px-3 py-1.5 text-right">
                            @if($item->diferencia === null)
                                <span class="text-zinc-500 dark:text-zinc-400">-</span>
                            @else
                                @php $dif = (float) $item->diferencia; @endphp
                                <span class="font-semibold {{ $dif == 0 ? 'text-emerald-600 dark:text-emerald-400' : ($dif > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400') }}">
                                    {{ $dif > 0 ? '+' : '' }}{{ number_format($dif, 2) }}
                                </span>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $dif == 0 ? 'Cuadra' : ($dif > 0 ? 'Sobrante' : 'Faltante') }}</div>
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
                            @elseif($item->closed_at)
                                <div class="text-zinc-800 dark:text-zinc-100">{{ \Carbon\Carbon::parse($item->closed_at)->format('d/m/Y') }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ \Carbon\Carbon::parse($item->closed_at)->format('H:i') }}</div>
                            @else
                                <span class="text-zinc-500 dark:text-zinc-400">-</span>
                            @endif
                        </td>

                        @role('admin')
                        {{-- Solo indica si tiene observación (el texto se ve al pasar el mouse o al hacer clic) --}}
                        <td class="px-3 py-1.5 text-center">
                            @if($item->observacion)
                                <button type="button" wire:click="openNoteModal({{ $item->id }})"
                                    class="cursor-pointer rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-700 dark:bg-sky-900/30 dark:text-sky-300"
                                    title="{{ $item->observacion }}">
                                    <i class="bi bi-journal-text"></i> Sí
                                </button>
                            @else
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>
                        @endrole

                        <td class="px-3 py-1.5">
                            <div class="flex justify-center gap-3">

                                @role('admin')
                                <button wire:click="openNoteModal({{ $item->id }})"
                                    class="cursor-pointer {{ $item->observacion ? 'text-primary' : 'text-zinc-500 hover:text-primary dark:text-zinc-400' }}"
                                    title="{{ $item->observacion ? 'Editar observación' : 'Agregar observación' }}">
                                    <i class="bi {{ $item->observacion ? 'bi-journal-text' : 'bi-journal-plus' }} text-lg"></i>
                                </button>

                                <a href="{{ route('cash-registers.sales', $item->id) }}" wire:navigate
                                    class="cursor-pointer text-emerald-600 hover:text-emerald-800 dark:text-emerald-400"
                                    title="Ver ventas detalladas de esta caja">
                                    <i class="bi bi-receipt text-lg"></i>
                                </a>
                                @endrole

                                @can('Ver Movimientos Caja')
                                <button wire:click="openDetailModal({{ $item->id }})"
                                    class="cursor-pointer text-sky-600 hover:text-sky-800 dark:text-sky-400"
                                    title="Ver detalle">
                                    <i class="bi bi-eye text-lg"></i>
                                </button>
                                @endcan

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
                                    @endcan

                                    @can('Cerrar Caja')
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
                        <td colspan="10" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            No hay cajas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if($cashRegisters->count())
                @php $restCols = auth()->user()?->hasRole('admin') ? 6 : 5; @endphp
                {{-- Totales de ventas (sin el monto inicial): efectivo y QR, y debajo el total de ambos --}}
                <tfoot class="bg-zinc-300 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-100">
                    <tr class="border-t border-zinc-400 dark:border-zinc-600">
                        <td colspan="3" class="px-3 py-2 font-semibold">
                            Total efectivo / QR
                            <span class="text-xs font-normal text-zinc-600 dark:text-zinc-400">({{ $grandTotals['cajas'] }} cajas{{ trim($search) !== '' ? ', según la búsqueda' : '' }})</span>
                        </td>
                        <td class="col-ef px-3 py-2 text-right font-bold">{{ number_format($grandTotals['efectivo'], 2) }}</td>
                        <td class="col-qr px-3 py-2 text-right font-bold">{{ number_format($grandTotals['qr'], 2) }}</td>
                        <td colspan="{{ $restCols }}"></td>
                    </tr>
                    <tr class="border-t border-zinc-400 dark:border-zinc-600">
                        <td colspan="3" class="px-3 py-2 font-bold">TOTAL (efectivo + QR)</td>
                        <td colspan="2" class="col-tot px-3 py-2 text-center text-lg font-bold">
                            {{ number_format($grandTotals['efectivo'] + $grandTotals['qr'], 2) }}
                        </td>
                        <td colspan="{{ $restCols }}" class="px-3 py-2 text-xs text-zinc-600 dark:text-zinc-400">Solo ventas, sin el monto inicial</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    {{-- Tarjetas responsive (solo en móvil) --}}
    <div class="flex flex-col gap-2 md:hidden">
        @forelse ($cashRegisters as $item)
            <div x-data="{ open: false }" class="rounded border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <button type="button" x-on:click.stop="open = !open"
                        class="flex h-6 w-6 shrink-0 cursor-pointer items-center justify-center rounded-full border border-primary text-primary transition hover:bg-primary hover:text-white">
                        <i class="bi text-xs" :class="open ? 'bi-dash' : 'bi-plus'"></i>
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ \Carbon\Carbon::parse($item->fecha_apertura)->format('d/m/Y') }} <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->created_at?->format('H:i') }}</span></p>
                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $item->user?->name ?? '-' }}</p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3">
                        @role('admin')
                        <button wire:click="openNoteModal({{ $item->id }})"
                            class="cursor-pointer {{ $item->observacion ? 'text-primary' : 'text-zinc-500 dark:text-zinc-400' }}"
                            title="Observación">
                            <i class="bi {{ $item->observacion ? 'bi-journal-text' : 'bi-journal-plus' }} text-lg"></i>
                        </button>
                        <a href="{{ route('cash-registers.sales', $item->id) }}" wire:navigate
                            class="cursor-pointer text-emerald-600 dark:text-emerald-400" title="Ventas detalladas">
                            <i class="bi bi-receipt text-lg"></i>
                        </a>
                        @endrole

                        @can('Ver Movimientos Caja')
                        <button wire:click="openDetailModal({{ $item->id }})"
                            class="cursor-pointer text-sky-600 hover:text-sky-800 dark:text-sky-400"
                            title="Ver detalle">
                            <i class="bi bi-eye text-lg"></i>
                        </button>
                        @endcan

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
                            @endcan

                            @can('Cerrar Caja')
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
                </div>

                <div x-show="open" x-collapse x-cloak class="mt-2 grid grid-cols-2 gap-2 border-t border-zinc-200 pt-2 pl-8 dark:border-zinc-800">
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Inicial</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ number_format($item->monto_apertura, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Ventas</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">
                            Ef. {{ number_format((float) $item->ventas_efectivo, 2) }} · QR {{ number_format((float) $item->ventas_qr, 2) }}
                            <span class="block font-semibold">Total {{ number_format((float) $item->ventas_efectivo + (float) $item->ventas_qr, 2) }}</span>
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Contado</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->monto_cierre !== null ? number_format($item->monto_cierre, 2) : '-' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Diferencia</span>
                        <p class="text-sm">
                            @if($item->diferencia === null)
                                <span class="text-zinc-500 dark:text-zinc-400">-</span>
                            @else
                                @php $dif = (float) $item->diferencia; @endphp
                                <span class="font-semibold {{ $dif == 0 ? 'text-emerald-600 dark:text-emerald-400' : ($dif > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400') }}">
                                    {{ $dif > 0 ? '+' : '' }}{{ number_format($dif, 2) }}
                                    ({{ $dif == 0 ? 'cuadra' : ($dif > 0 ? 'sobrante' : 'faltante') }})
                                </span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Estado</span>
                        <p>
                            @if($item->status === 'OPEN')
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    Abierta
                                </span>
                            @else
                                <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    Cerrada
                                </span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Fin</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">
                            @if($item->status === 'OPEN' && $item->expires_at)
                                {{ $item->expires_at->format('d/m/Y H:i') }}
                                @if($item->isExpired())
                                    <span class="mt-1 inline-block rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">Vencida</span>
                                @else
                                    <span class="mt-1 inline-block rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Vigente</span>
                                @endif
                            @elseif($item->closed_at)
                                {{ \Carbon\Carbon::parse($item->closed_at)->format('d/m/Y H:i') }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    @role('admin')
                        @if($item->observacion)
                            <div class="col-span-2">
                                <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Observación</span>
                                <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->observacion }}</p>
                            </div>
                        @endif
                    @endrole
                </div>
            </div>
        @empty
            <div class="rounded border border-zinc-300 bg-white px-3 py-4 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                No hay cajas registradas.
            </div>
        @endforelse
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

    @if($noteModalVisible)
        @include('livewire.caja.modal.note')
    @endif

</div>
