<div class="p-6">
    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <a href="{{ route('clients.create') }}" wire:navigate
            class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2 text-center text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600">
            <i class="bi bi-person-plus-fill mr-1"></i> Nuevo cliente
        </a>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar cliente..."
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none md:w-96 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-400"
        >
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">PERSONA</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">CÓDIGO</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">TIPO</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">NIT</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">EMAIL</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">ESTADO</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">OPCIONES</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                @forelse ($clients as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/70">
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->person?->name }} {{ $item->person?->lastname }}
                            <div class="text-xs text-gray-500 dark:text-zinc-400">
                                CI: {{ $item->person?->ci ?? '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">{{ $item->code }}</td>
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">{{ strtoupper($item->type) }}</td>
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">{{ $item->nit ?: '-' }}</td>
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">{{ $item->email ?: '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $item->state ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $item->state ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-center gap-3">
                                <a
                                    href="{{ route('clients.edit', $item->id) }}" wire:navigate
                                    class="cursor-pointer text-yellow-500 transition hover:text-yellow-600 dark:text-yellow-400 dark:hover:text-yellow-300">
                                    <i class="bi bi-pencil-square text-lg"></i>
                                </a>

                                <button
                                    wire:click="delete({{ $item->id }})"
                                    class="{{ $item->state ? 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300' : 'text-gray-400 dark:text-zinc-500' }} cursor-pointer">
                                    <i class="bi {{ $item->state ? 'bi-trash' : 'bi-arrow-clockwise' }} text-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-4 text-center text-gray-500 dark:text-zinc-400">
                            No hay clientes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $clients->links() }}
    </div>

</div>