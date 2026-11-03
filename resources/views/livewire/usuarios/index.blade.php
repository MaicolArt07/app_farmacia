<div class="flex flex-col bg-zinc-200 p-4 dark:bg-zinc-950">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
        <!-- Boton Crear -->
        <a href="{{ route('users.create') }}" wire:navigate
            class="flex items-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
            <i class="bi bi-plus-circle text-primary"></i> Nuevo usuario
        </a>

        <!-- Buscador -->
        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por nombre o correo..."
            class="w-full rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-primary focus:outline-none md:w-80 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500"
        />
    </div>

    <!-- Tabla (md en adelante) -->
    <div class="hidden overflow-auto rounded border border-zinc-300 md:block dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Nombre</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Correo</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Persona</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Permisos</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Opciones</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($users as $user)
                    <tr wire:click="view({{ $user->id }})" class="cursor-pointer border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <td class="px-3 py-1.5 capitalize text-zinc-800 dark:text-zinc-100">{{ $user->name }}</td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $user->email }}</td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            @if ($user->person)
                                {{ trim($user->person->nombre . ' ' . $user->person->apellido) }}
                            @else
                                <span class="text-zinc-400 dark:text-zinc-500">&mdash;</span>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 text-center">
                            <span class="rounded-full bg-zinc-200 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                {{ $user->getAllPermissions()->count() }} permiso(s)
                            </span>
                        </td>
                        <td class="px-3 py-1.5" onclick="event.stopPropagation()">
                            <div class="flex justify-center gap-3">
                                <a
                                    href="{{ route('users.edit', $user->id) }}" wire:navigate
                                    class="cursor-pointer text-zinc-500 transition hover:text-primary dark:text-zinc-400"
                                    title="Editar">
                                    <i class="bi bi-pencil-square text-base"></i>
                                </a>

                                <button
                                    type="button"
                                    x-on:click.prevent="confirmAction({
                                        title: '¿Eliminar usuario?',
                                        confirmText: 'Sí, eliminar',
                                    }).then((ok) => { if (ok) $wire.delete({{ $user->id }}) })"
                                    class="cursor-pointer text-zinc-500 transition hover:text-red-600 dark:text-zinc-400"
                                    title="Eliminar">
                                    <i class="bi bi-trash text-base"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">No se encontraron usuarios.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tarjetas responsive (solo en móvil) --}}
    <div class="flex flex-col gap-2 md:hidden">
        @forelse ($users as $user)
            <div
                x-data="{ open: false }"
                wire:click="view({{ $user->id }})"
                class="cursor-pointer rounded border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div class="flex items-center gap-2">
                    <button type="button" x-on:click.stop="open = !open"
                        class="flex h-6 w-6 shrink-0 cursor-pointer items-center justify-center rounded-full border border-primary text-primary transition hover:bg-primary hover:text-white">
                        <i class="bi text-xs" :class="open ? 'bi-dash' : 'bi-plus'"></i>
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium capitalize text-zinc-800 dark:text-zinc-100">{{ $user->name }}</p>
                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3" onclick="event.stopPropagation()">
                        <a
                            href="{{ route('users.edit', $user->id) }}" wire:navigate
                            class="cursor-pointer text-zinc-500 transition hover:text-primary dark:text-zinc-400"
                            title="Editar">
                            <i class="bi bi-pencil-square text-base"></i>
                        </a>

                        <button
                            type="button"
                            x-on:click.prevent="confirmAction({
                                title: '¿Eliminar usuario?',
                                confirmText: 'Sí, eliminar',
                            }).then((ok) => { if (ok) $wire.delete({{ $user->id }}) })"
                            class="cursor-pointer text-zinc-500 transition hover:text-red-600 dark:text-zinc-400"
                            title="Eliminar">
                            <i class="bi bi-trash text-base"></i>
                        </button>
                    </div>
                </div>

                <div x-show="open" x-collapse x-cloak onclick="event.stopPropagation()" class="mt-2 grid grid-cols-2 gap-2 border-t border-zinc-200 pt-2 pl-8 dark:border-zinc-800">
                    <div class="col-span-2">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Persona</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">
                            @if ($user->person)
                                {{ trim($user->person->nombre . ' ' . $user->person->apellido) }}
                            @else
                                &mdash;
                            @endif
                        </p>
                    </div>
                    <div class="col-span-2">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Permisos</span>
                        <p>
                            <span class="rounded-full bg-zinc-200 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                {{ $user->getAllPermissions()->count() }} permiso(s)
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded border border-zinc-300 bg-white px-3 py-4 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                No se encontraron usuarios.
            </div>
        @endforelse
    </div>

    <!-- Paginacion -->
    <div class="mt-4 dark:text-zinc-200">
        {{ $users->links() }}
    </div>

    {{-- Modal de detalle de usuario --}}
    <div x-data="{ open: false }" x-init="$watch('$wire.viewingId', value => open = !!value)" x-show="!!$wire.viewingId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-zinc-900/30" wire:click="closeView"></div>

        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="relative flex w-full max-w-lg flex-col rounded border border-zinc-300 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900 max-h-[90vh]">

            @if ($viewing)
                <div class="flex items-center justify-between border-b border-zinc-300 bg-zinc-200 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Detalle de usuario</h2>
                    <button wire:click="closeView" class="text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="space-y-4 overflow-y-auto px-4 py-5">
                    <div class="space-y-1">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Nombre</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->name }}</p>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Correo</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->email }}</p>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Persona asociada</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">
                            {{ $viewing->person ? trim($viewing->person->nombre . ' ' . $viewing->person->apellido) . ' - CI: ' . $viewing->person->ci : '-' }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Roles ({{ $viewing->roles->count() }})</span>
                        <div class="flex flex-wrap gap-1">
                            @forelse ($viewing->roles as $role)
                                <span class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium capitalize text-primary">{{ $role->name }}</span>
                            @empty
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">Sin roles asignados.</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Permisos totales ({{ $viewing->getAllPermissions()->count() }})</span>
                        <div class="flex flex-wrap gap-1">
                            @forelse ($viewing->getAllPermissions() as $permission)
                                <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ $permission->name }}</span>
                            @empty
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">Sin permisos asignados.</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Creado</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Actualizado</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->updated_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-zinc-300 bg-zinc-100 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <button wire:click="closeView" class="rounded border border-zinc-400 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 cursor-pointer">
                        Cerrar
                    </button>
                </div>
            @endif
        </div>
    </div>

</div>