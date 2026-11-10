<div class="flex flex-col gap-4 bg-zinc-200 p-4 dark:bg-zinc-950">

    <div class="rounded border border-zinc-300 bg-zinc-100 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
        <h1 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
            <i class="bi bi-diagram-3-fill text-primary"></i> Configuración de Sucursales
        </h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            Herramienta de un solo uso (solo Super Admin) para separar los datos actuales en dos sucursales reales:
            la sucursal donde está cargado todo hoy ("La Moscú") y la nueva Casa Matriz.
        </p>
    </div>

    {{-- Estado actual --}}
    <div class="overflow-auto rounded border border-zinc-300 dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Sucursal</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">N° Sucursal</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Usuarios</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Lotes</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Cajas</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Ventas</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($sucursales as $s)
                    <tr class="border-b border-zinc-200 dark:border-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $s->nombre }}</td>
                        <td class="px-3 py-1.5 text-center text-zinc-700 dark:text-zinc-300">{{ $s->numero_sucursal }}</td>
                        <td class="px-3 py-1.5 text-center text-zinc-700 dark:text-zinc-300">{{ $s->users_count }}</td>
                        <td class="px-3 py-1.5 text-center text-zinc-700 dark:text-zinc-300">{{ $s->lots_count }}</td>
                        <td class="px-3 py-1.5 text-center text-zinc-700 dark:text-zinc-300">{{ $s->cash_registers_count }}</td>
                        <td class="px-3 py-1.5 text-center text-zinc-700 dark:text-zinc-300">{{ $s->sales_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">No hay sucursales registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($yaMigrado)
        <div class="flex items-center gap-2 rounded border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
            <i class="bi bi-check-circle-fill"></i>
            Ya existen {{ $sucursales->count() }} sucursales registradas. La migración inicial ya fue ejecutada y no se puede volver a correr desde aquí.
        </div>

        @if (array_sum($pendientes) > 0)
            <div class="rounded border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Aún hay registros sin sucursal asignada: {{ $pendientes['users'] }} usuarios, {{ $pendientes['lotes'] }} lotes,
                {{ $pendientes['cajas'] }} cajas, {{ $pendientes['ventas'] }} ventas. Revisalos manualmente desde cada módulo.
            </div>
        @endif
    @else
        <div class="rounded border border-zinc-300 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-3 text-sm font-semibold text-zinc-800 dark:text-zinc-100">Datos para la migración</h2>

            <div class="mb-4 rounded border border-amber-300 bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                <i class="bi bi-info-circle-fill"></i>
                Verificá el <strong>N° de Sucursal</strong> y <strong>Punto de Venta</strong> con los datos que te dio el SIN para
                cada local antes de ejecutar esto — son los que se usan para la facturación electrónica.
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- Moscú --}}
                <div class="space-y-3 rounded border border-zinc-300 p-3 dark:border-zinc-700">
                    <h3 class="text-sm font-semibold text-primary">Sucursal donde está todo hoy</h3>

                    <div class="space-y-1">
                        <label class="text-xs font-medium text-zinc-700 dark:text-zinc-200">Nombre</label>
                        <input type="text" wire:model="moscu_nombre" class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                        @error('moscu_nombre') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-zinc-700 dark:text-zinc-200">N° Sucursal (SIN)</label>
                            <input type="number" wire:model="moscu_numero_sucursal" class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-zinc-700 dark:text-zinc-200">Punto de Venta</label>
                            <input type="number" wire:model="moscu_punto_venta" class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium text-zinc-700 dark:text-zinc-200">Municipio</label>
                        <input type="text" wire:model="moscu_municipio" class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-zinc-700 dark:text-zinc-200">Dirección</label>
                        <input type="text" wire:model="moscu_direccion" class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-zinc-700 dark:text-zinc-200">Teléfono</label>
                        <input type="text" wire:model="moscu_telefono" class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                </div>

                {{-- Casa Matriz --}}
                <div class="space-y-3 rounded border border-zinc-300 p-3 dark:border-zinc-700">
                    <h3 class="text-sm font-semibold text-primary">Casa Matriz (nueva, sin stock todavía)</h3>

                    <div class="space-y-1">
                        <label class="text-xs font-medium text-zinc-700 dark:text-zinc-200">Nombre</label>
                        <input type="text" wire:model="matriz_nombre" class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                        @error('matriz_nombre') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-zinc-700 dark:text-zinc-200">N° Sucursal (SIN)</label>
                            <input type="number" wire:model="matriz_numero_sucursal" class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-zinc-700 dark:text-zinc-200">Punto de Venta</label>
                            <input type="number" wire:model="matriz_punto_venta" class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium text-zinc-700 dark:text-zinc-200">Municipio</label>
                        <input type="text" wire:model="matriz_municipio" class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-zinc-700 dark:text-zinc-200">Dirección</label>
                        <input type="text" wire:model="matriz_direccion" class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-zinc-700 dark:text-zinc-200">Teléfono</label>
                        <input type="text" wire:model="matriz_telefono" class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="button" wire:click="askConfirmation"
                    class="rounded border border-primary bg-white px-4 py-2 text-sm font-semibold text-primary shadow-sm transition hover:bg-primary/10 cursor-pointer dark:bg-zinc-800 dark:hover:bg-primary/20">
                    <i class="bi bi-play-fill"></i> Revisar y ejecutar migración
                </button>
            </div>
        </div>
    @endif

    {{-- Modal de confirmación --}}
    <div x-data="{ open: false }" x-init="$watch('$wire.confirming', value => open = value)" x-show="$wire.confirming" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-zinc-900/50" wire:click="cancelConfirmation"></div>

        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative flex w-full max-w-lg flex-col rounded border border-red-400 bg-white shadow-xl dark:border-red-800 dark:bg-zinc-900">

            <div class="flex items-center justify-between border-b border-zinc-300 bg-red-50 px-4 py-3 dark:border-zinc-700 dark:bg-red-900/20">
                <h2 class="text-sm font-semibold text-red-700 dark:text-red-300">
                    <i class="bi bi-exclamation-triangle-fill"></i> Confirmar migración de sucursales
                </h2>
            </div>

            <div class="space-y-3 px-4 py-5 text-sm text-zinc-700 dark:text-zinc-200">
                <p>Esto va a:</p>
                <ul class="list-disc space-y-1 pl-5">
                    <li>Renombrar la sucursal actual a <strong>"{{ $moscu_nombre }}"</strong>.</li>
                    <li>Crear la nueva sucursal <strong>"{{ $matriz_nombre }}"</strong> (vacía, sin stock).</li>
                    <li>Asignar <strong>todos</strong> los usuarios, lotes, cajas y ventas que hoy no tienen sucursal a <strong>"{{ $moscu_nombre }}"</strong>.</li>
                </ul>
                <p class="font-medium text-red-600 dark:text-red-400">Esta acción no se puede deshacer desde aquí. ¿Confirmás que los datos son correctos?</p>
            </div>

            <div class="flex justify-end gap-3 border-t border-zinc-300 bg-zinc-100 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <button wire:click="cancelConfirmation" class="rounded border border-zinc-400 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 cursor-pointer">
                    Cancelar
                </button>
                <button wire:click="ejecutarMigracion" class="rounded border border-red-600 bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 cursor-pointer">
                    <i class="bi bi-check-lg"></i> Sí, ejecutar migración
                </button>
            </div>
        </div>
    </div>
</div>
