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

    <!-- Tabla -->
    <div class="overflow-auto rounded border border-zinc-300 dark:border-zinc-700">
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
                    <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
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
                        <td class="px-3 py-1.5">
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

    <!-- Paginacion -->
    <div class="mt-4 dark:text-zinc-200">
        {{ $users->links() }}
    </div>

</div>