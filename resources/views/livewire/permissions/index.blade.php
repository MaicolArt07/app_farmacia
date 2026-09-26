<div class="p-6">
    @if (session()->has('message'))
        <div class="mb-4 cursor-pointer rounded bg-green-100 p-3 text-green-800 shadow dark:bg-green-900/30 dark:text-green-300">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <!-- Boton Crear -->
        <a
            href="{{ route('permissions.create') }}" wire:navigate
            class="flex w-full cursor-pointer items-center justify-center gap-2 rounded bg-green-600 px-4 py-2 text-white transition hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 sm:w-auto"
        >
            <i class="bi bi-plus-circle"></i>
        </a>

        <!-- Buscador -->
        <div class="relative w-full sm:w-1/2">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-zinc-400">
                <i class="bi bi-search"></i>
            </span>
            <input
                type="text"
                wire:model.live="search"
                placeholder="Buscar permiso..."
                class="w-full rounded border border-gray-300 bg-white py-2 pl-10 pr-3 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-400"
            />
        </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto rounded-lg">
        <table class="min-w-full overflow-hidden rounded-lg border border-gray-300 shadow-sm dark:border-zinc-700">
            <thead class="bg-blue-50 dark:bg-zinc-800">
                <tr>
                    <th class="border border-gray-300 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-blue-700 dark:border-zinc-700 dark:text-blue-300">
                        Nombre del Permiso
                    </th>
                    <th class="border border-gray-300 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-blue-700 dark:border-zinc-700 dark:text-blue-300">
                        Acciones
                    </th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($permissions as $permission)
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-zinc-800">
                        <td class="border border-gray-200 px-4 py-2 text-sm capitalize text-gray-800 dark:border-zinc-700 dark:text-zinc-100">
                            {{ $permission->name }}
                        </td>

                        <td class="border border-gray-200 px-4 py-2 text-center dark:border-zinc-700">
                            <a
                                href="{{ route('permissions.edit', $permission->id) }}" wire:navigate
                                class="cursor-pointer text-yellow-500 transition hover:text-yellow-600 dark:text-yellow-400 dark:hover:text-yellow-300"
                                title="Editar"
                            >
                                <i class="bi bi-pencil-square text-lg"></i>
                            </a>

                            <button
                                type="button"
                                x-on:click.prevent="confirmAction({
                                    title: '¿Eliminar este permiso?',
                                    confirmText: 'Sí, eliminar',
                                }).then((ok) => { if (ok) $wire.delete({{ $permission->id }}) })"
                                class="ml-2 cursor-pointer text-red-500 transition hover:text-red-600 dark:text-red-400 dark:hover:text-red-300"
                                title="Eliminar"
                                type="button"
                            >
                                <i class="bi bi-trash text-lg"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="py-4 text-center text-gray-500 dark:text-zinc-400">
                            No se encontraron permisos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $permissions->links() }}
    </div>

</div>