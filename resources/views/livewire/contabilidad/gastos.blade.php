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
        <button type="button" wire:click="create"
            class="flex items-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
            <i class="bi bi-receipt-cutoff text-primary"></i> Nuevo gasto común
        </button>
        <p class="text-xs text-zinc-500 dark:text-zinc-400">
            Son los gastos que se repiten cada mes (alquiler, luz, agua, internet, etc). Para registrar el gasto real
            de un mes, usa "Registrar movimiento" en el Libro Diario.
        </p>
    </div>

    <div class="hidden overflow-auto rounded border border-zinc-300 md:block dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Nombre</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Descripción</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Monto referencia</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Estado</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Opciones</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($gastos as $item)
                    <tr class="border-b border-zinc-200 dark:border-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $item->nombre }}</td>
                        <td class="px-3 py-1.5 text-zinc-600 dark:text-zinc-300">{{ $item->descripcion ?: '-' }}</td>
                        <td class="px-3 py-1.5 text-center text-zinc-800 dark:text-zinc-100">
                            {{ $item->monto_referencia !== null ? 'Bs. ' . number_format($item->monto_referencia, 2) : '-' }}
                        </td>
                        <td class="px-3 py-1.5 text-center">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $item->estado ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $item->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-3 py-1.5">
                            <div class="flex justify-center gap-3">
                                <button type="button" wire:click="edit({{ $item->id }})"
                                    class="cursor-pointer text-zinc-500 transition hover:text-primary dark:text-zinc-400">
                                    <i class="bi bi-pencil-square text-base"></i>
                                </button>
                                <button
                                    type="button"
                                    x-on:click.prevent="confirmAction({
                                        text: '¿Deseas {{ $item->estado ? 'desactivar' : 'activar' }} este registro?',
                                        confirmText: 'Sí, {{ $item->estado ? 'desactivar' : 'activar' }}',
                                    }).then((ok) => { if (ok) $wire.delete({{ $item->id }}) })"
                                    class="cursor-pointer text-zinc-500 transition hover:text-red-600 dark:text-zinc-400">
                                    <i class="bi {{ $item->estado ? 'bi-trash' : 'bi-arrow-clockwise' }} text-base"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            No hay gastos comunes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex flex-col gap-2 md:hidden">
        @forelse ($gastos as $item)
            <div class="rounded border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $item->nombre }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $item->monto_referencia !== null ? 'Bs. ' . number_format($item->monto_referencia, 2) : 'Sin monto referencia' }}
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <button type="button" wire:click="edit({{ $item->id }})" class="cursor-pointer text-zinc-500 hover:text-primary dark:text-zinc-400">
                            <i class="bi bi-pencil-square text-base"></i>
                        </button>
                        <button
                            type="button"
                            x-on:click.prevent="confirmAction({
                                text: '¿Deseas {{ $item->estado ? 'desactivar' : 'activar' }} este registro?',
                                confirmText: 'Sí, {{ $item->estado ? 'desactivar' : 'activar' }}',
                            }).then((ok) => { if (ok) $wire.delete({{ $item->id }}) })"
                            class="cursor-pointer text-zinc-500 hover:text-red-600 dark:text-zinc-400">
                            <i class="bi {{ $item->estado ? 'bi-trash' : 'bi-arrow-clockwise' }} text-base"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded border border-zinc-300 bg-white px-3 py-4 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                No hay gastos comunes registrados.
            </div>
        @endforelse
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $gastos->links() }}
    </div>

    {{-- Modal --}}
    <div x-data="{ open: false }" x-init="$watch('$wire.modalVisible', value => open = value)" x-show="$wire.modalVisible" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-zinc-900/30" wire:click="cancel"></div>

        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative flex w-full max-w-lg flex-col rounded border border-zinc-300 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">

            <div class="flex items-center justify-between border-b border-zinc-300 bg-zinc-200 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ $form->id ? 'Editar gasto común' : 'Nuevo gasto común' }}
                </h2>
                <button wire:click="cancel" class="text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="space-y-4 px-4 py-5">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Nombre</label>
                    <input type="text" wire:model="form.nombre" placeholder="Ej: Alquiler, Luz, Agua, Internet..."
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.nombre') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Monto de referencia (Opcional)</label>
                    <input type="number" step="0.01" min="0" wire:model="form.monto_referencia"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Se usa para precargar el monto al registrar el gasto real del mes.</p>
                    @error('form.monto_referencia') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Descripción (Opcional)</label>
                    <textarea wire:model="form.descripcion" rows="2"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                    @error('form.descripcion') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-zinc-300 bg-zinc-100 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <button wire:click="cancel" class="rounded border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20 cursor-pointer">
                    Cancelar
                </button>
                <button wire:click="save" class="rounded border border-primary bg-white px-4 py-2 text-sm font-semibold text-primary shadow-sm transition hover:bg-primary/10 cursor-pointer dark:bg-zinc-800 dark:hover:bg-primary/20">
                    <i class="bi bi-check-lg text-primary"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
