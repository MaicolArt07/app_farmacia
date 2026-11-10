<div class="flex flex-col bg-zinc-200 p-4 dark:bg-zinc-950">

    {{-- Tabs --}}
    <div class="mb-3 flex gap-2">
        <button type="button" wire:click="$set('tab', 'libro')"
            class="rounded border px-3 py-1.5 text-sm font-medium {{ $tab === 'libro' ? 'border-primary bg-primary text-white' : 'border-zinc-400 bg-white text-zinc-700 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200' }}">
            <i class="bi bi-journal-text"></i> Libro Diario
        </button>
        <button type="button" wire:click="$set('tab', 'gastos')"
            class="rounded border px-3 py-1.5 text-sm font-medium {{ $tab === 'gastos' ? 'border-primary bg-primary text-white' : 'border-zinc-400 bg-white text-zinc-700 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200' }}">
            <i class="bi bi-receipt"></i> Gastos Comunes
        </button>
    </div>

    <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
        <button type="button" wire:click="openMovModal"
            class="flex items-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
            <i class="bi bi-plus-circle text-primary"></i> Registrar movimiento
        </button>

        <div class="flex items-center gap-2">
            <label class="text-sm text-zinc-600 dark:text-zinc-300">Mes</label>
            <input type="month" wire:model.live="mes"
                class="rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
        </div>
    </div>

    {{-- Resumen --}}
    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded border border-red-300 bg-red-50 px-4 py-3 dark:border-red-900/50 dark:bg-red-900/10">
            <p class="text-xs font-medium uppercase tracking-wide text-red-700 dark:text-red-300">Debe (gastos)</p>
            <p class="text-xl font-bold text-red-700 dark:text-red-300">Bs. {{ number_format($debe, 2) }}</p>
        </div>
        <div class="rounded border border-emerald-300 bg-emerald-50 px-4 py-3 dark:border-emerald-900/50 dark:bg-emerald-900/10">
            <p class="text-xs font-medium uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Haber (ingresos)</p>
            <p class="text-xl font-bold text-emerald-700 dark:text-emerald-300">Bs. {{ number_format($haber, 2) }}</p>
        </div>
        <div class="rounded border px-4 py-3 {{ $saldo >= 0 ? 'border-primary bg-primary/10' : 'border-red-400 bg-red-100 dark:bg-red-900/20' }}">
            <p class="text-xs font-medium uppercase tracking-wide {{ $saldo >= 0 ? 'text-primary' : 'text-red-700 dark:text-red-300' }}">Saldo del mes</p>
            <p class="text-xl font-bold {{ $saldo >= 0 ? 'text-primary' : 'text-red-700 dark:text-red-300' }}">Bs. {{ number_format($saldo, 2) }}</p>
        </div>
    </div>

    {{-- Tabla desktop --}}
    <div class="hidden overflow-auto rounded border border-zinc-300 md:block dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Fecha</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Concepto</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Origen</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-right font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Debe</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-right font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Haber</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Opciones</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($movimientos as $mov)
                    <tr class="border-b border-zinc-200 dark:border-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $mov->fecha->format('d/m/Y') }}</td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $mov->concepto }}</td>
                        <td class="px-3 py-1.5 text-center">
                            <span class="rounded-full bg-zinc-200 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">
                                {{ ['COMPRA' => 'Compra', 'CIERRE_CAJA' => 'Cierre de caja', 'GASTO_COMUN' => 'Gasto común', 'MANUAL' => 'Manual'][$mov->origen] ?? $mov->origen }}
                            </span>
                        </td>
                        <td class="px-3 py-1.5 text-right font-medium text-red-600 dark:text-red-400">
                            {{ $mov->tipo === 'DEBE' ? number_format($mov->monto, 2) : '-' }}
                        </td>
                        <td class="px-3 py-1.5 text-right font-medium text-emerald-600 dark:text-emerald-400">
                            {{ $mov->tipo === 'HABER' ? number_format($mov->monto, 2) : '-' }}
                        </td>
                        <td class="px-3 py-1.5 text-center">
                            @if (!in_array($mov->origen, ['COMPRA', 'CIERRE_CAJA']))
                                <button
                                    type="button"
                                    x-on:click.prevent="confirmAction({
                                        text: '¿Eliminar este movimiento?',
                                        confirmText: 'Sí, eliminar',
                                    }).then((ok) => { if (ok) $wire.deleteMovimiento({{ $mov->id }}) })"
                                    class="cursor-pointer text-zinc-500 transition hover:text-red-600 dark:text-zinc-400">
                                    <i class="bi bi-trash text-base"></i>
                                </button>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-600"><i class="bi bi-lock-fill"></i></span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            No hay movimientos registrados en este mes.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tarjetas móvil --}}
    <div class="flex flex-col gap-2 md:hidden">
        @forelse ($movimientos as $mov)
            <div class="rounded border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $mov->concepto }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $mov->fecha->format('d/m/Y') }} · {{ ['COMPRA' => 'Compra', 'CIERRE_CAJA' => 'Cierre de caja', 'GASTO_COMUN' => 'Gasto común', 'MANUAL' => 'Manual'][$mov->origen] ?? $mov->origen }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold {{ $mov->tipo === 'DEBE' ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ $mov->tipo === 'DEBE' ? '-' : '+' }} Bs. {{ number_format($mov->monto, 2) }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded border border-zinc-300 bg-white px-3 py-4 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                No hay movimientos registrados en este mes.
            </div>
        @endforelse
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $movimientos->links() }}
    </div>

    {{-- Modal: registrar movimiento --}}
    <div x-data="{ open: false }" x-init="$watch('$wire.movModalVisible', value => open = value)" x-show="$wire.movModalVisible" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-zinc-900/30" wire:click="closeMovModal"></div>

        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative flex w-full max-w-lg flex-col rounded border border-zinc-300 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">

            <div class="flex items-center justify-between border-b border-zinc-300 bg-zinc-200 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Registrar movimiento</h2>
                <button wire:click="closeMovModal" class="text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="space-y-4 px-4 py-5">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Tipo</label>
                    <select wire:model="mov_tipo"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="DEBE">Debe (gasto)</option>
                        <option value="HABER">Haber (ingreso)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Gasto común (Opcional)</label>
                    <select wire:model.live="mov_id_gasto_comun"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="">-- Movimiento manual (sin catálogo) --</option>
                        @foreach ($gastosComunes as $g)
                            <option value="{{ $g->id }}">{{ $g->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Concepto</label>
                    <input type="text" wire:model="mov_concepto"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('mov_concepto') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Monto (Bs.)</label>
                        <input type="number" step="0.01" min="0" wire:model="mov_monto"
                            class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                        @error('mov_monto') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Fecha</label>
                        <input type="date" wire:model="mov_fecha"
                            class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                        @error('mov_fecha') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-zinc-300 bg-zinc-100 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <button wire:click="closeMovModal" class="rounded border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20 cursor-pointer">
                    Cancelar
                </button>
                <button wire:click="saveMovimiento" class="rounded border border-primary bg-white px-4 py-2 text-sm font-semibold text-primary shadow-sm transition hover:bg-primary/10 cursor-pointer dark:bg-zinc-800 dark:hover:bg-primary/20">
                    <i class="bi bi-check-lg text-primary"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
