<div class="p-6">

    @if (session()->has('message'))
        <div class="mb-4 cursor-pointer rounded bg-green-100 p-3 text-green-800 shadow dark:bg-green-900/30 dark:text-green-300">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-4 flex justify-between">
        <a href="{{ route('person.create') }}" wire:navigate
            class="rounded bg-green-600 px-3 py-2 text-white transition hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 cursor-pointer">
            <i class="bi bi-person-plus-fill text-xl"></i>
        </a>

        <input type="text" wire:model.live="search"
            class="w-1/3 rounded border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-400"
            placeholder="Buscar persona...">
    </div>

    <div class="overflow-x-auto rounded-lg">
        <table class="min-w-full border border-gray-300 text-sm shadow-sm dark:border-zinc-700">
            <thead class="bg-gray-100 dark:bg-zinc-800">
            <tr>
                <th class="border border-gray-300 px-3 py-2 text-center text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">NOMBRE</th>
                <th class="border border-gray-300 px-3 py-2 text-center text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">APELLIDO</th>
                <th class="border border-gray-300 px-3 py-2 text-center text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">CI</th>
                <th class="border border-gray-300 px-3 py-2 text-center text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">TELEFONO</th>
                <th class="border border-gray-300 px-3 py-2 text-center text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">OPCIONES</th>
            </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
            @forelse ($persons as $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800">
                    <td class="border border-gray-300 px-3 py-2 text-zinc-800 dark:border-zinc-700 dark:text-zinc-100">{{ $item->name }}</td>
                    <td class="border border-gray-300 px-3 py-2 text-zinc-800 dark:border-zinc-700 dark:text-zinc-100">{{ $item->lastname }}</td>
                    <td class="border border-gray-300 px-3 py-2 text-zinc-800 dark:border-zinc-700 dark:text-zinc-100">{{ $item->ci }}</td>
                    <td class="border border-gray-300 px-3 py-2 text-zinc-800 dark:border-zinc-700 dark:text-zinc-100">{{ $item->phone }}</td>

                    <td class="border border-gray-300 px-3 py-2 text-center dark:border-zinc-700">
                        <div class="flex justify-center gap-3">
                            <!-- Editar -->
                            <a href="{{ route('person.edit', $item->id) }}" wire:navigate
                                    class="text-yellow-500 transition hover:text-yellow-600 dark:text-yellow-400 dark:hover:text-yellow-300 cursor-pointer">
                                <i class="bi bi-pencil-square text-xl"></i>
                            </a>

                            <!-- Activar / Desactivar -->
                            <button
                                type="button"
                                x-on:click.prevent="confirmAction({
                                    text: '¿Deseas {{ $item->state ? 'desactivar' : 'activar' }} este registro?',
                                    confirmText: 'Sí, {{ $item->state ? 'desactivar' : 'activar' }}',
                                }).then((ok) => { if (ok) $wire.delete({{ $item->id }}) })"
                                class="{{ $item->state ? 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300' : 'text-gray-400 cursor-not-allowed dark:text-zinc-500' }} cursor-pointer">
                                @if($item->state)
                                    <i class="bi bi-trash text-xl"></i>
                                @else
                                    <i class="bi bi-x-circle text-xl"></i>
                                @endif
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-3 text-center text-gray-500 dark:text-zinc-400">No hay personas.</td>
                </tr>
            @endforelse
            </tbody>

        </table>
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $persons->links() }}
    </div>


</div>