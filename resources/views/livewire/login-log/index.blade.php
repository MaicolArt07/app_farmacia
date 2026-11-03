<div class="flex flex-col bg-zinc-200 p-4 dark:bg-zinc-950">

    <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
        <h1 class="flex items-center gap-2 text-sm font-semibold text-zinc-700 dark:text-zinc-200">
            <i class="bi bi-shield-lock-fill text-primary"></i> Logs de acceso
        </h1>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por usuario, correo o IP..."
            class="w-full rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-primary focus:outline-none md:w-80 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500"
        >
    </div>

    @php
        $actionLabels = [
            'login' => ['Inicio de sesión', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'],
            'logout' => ['Cierre de sesión', 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200'],
            'forced_logout' => ['Cierre forzado', 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'],
        ];
    @endphp

    {{-- Tabla (md en adelante) --}}
    <div class="hidden overflow-auto rounded border border-zinc-300 md:block dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Usuario</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Acción</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">IP</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Navegador</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Sistema</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Dispositivo</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Fecha</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($logs as $item)
                    <tr class="border-b border-zinc-200 dark:border-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->user?->name ?? '-' }}
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->user?->email }}</div>
                        </td>
                        <td class="px-3 py-1.5">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $actionLabels[$item->action][1] ?? 'bg-zinc-200 text-zinc-700' }}">
                                {{ $actionLabels[$item->action][0] ?? $item->action }}
                            </span>
                        </td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $item->ip_address ?: '-' }}</td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $item->browser ?: '-' }}</td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $item->platform ?: '-' }}</td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $item->device ?: '-' }}</td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            No hay registros de acceso.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tarjetas responsive (solo en móvil) --}}
    <div class="flex flex-col gap-2 md:hidden">
        @forelse ($logs as $item)
            <div x-data="{ open: false }" class="rounded border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <button type="button" x-on:click.stop="open = !open"
                        class="flex h-6 w-6 shrink-0 cursor-pointer items-center justify-center rounded-full border border-primary text-primary transition hover:bg-primary hover:text-white">
                        <i class="bi text-xs" :class="open ? 'bi-dash' : 'bi-plus'"></i>
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $item->user?->name ?? '-' }}</p>
                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $item->created_at?->format('d/m/Y H:i') }}</p>
                    </div>

                    <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $actionLabels[$item->action][1] ?? 'bg-zinc-200 text-zinc-700' }}">
                        {{ $actionLabels[$item->action][0] ?? $item->action }}
                    </span>
                </div>

                <div x-show="open" x-collapse x-cloak class="mt-2 grid grid-cols-2 gap-2 border-t border-zinc-200 pt-2 pl-8 dark:border-zinc-800">
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Correo</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->user?->email ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">IP</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->ip_address ?: '-' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Navegador</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->browser ?: '-' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Sistema</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->platform ?: '-' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Dispositivo</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->device ?: '-' }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded border border-zinc-300 bg-white px-3 py-4 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                No hay registros de acceso.
            </div>
        @endforelse
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $logs->links() }}
    </div>

</div>
