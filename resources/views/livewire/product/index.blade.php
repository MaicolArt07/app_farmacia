<div class="p-6">
    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <a href="{{ route('products.create') }}" wire:navigate
            class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2 text-center text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600">
            <i class="bi bi-capsule-pill mr-1"></i> Nuevo producto
        </a>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por código, barra, nombre, genérico, categoría..."
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none md:w-[28rem] dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-400"
        >
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">CÓDIGO</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">PRODUCTO</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">CATEGORÍA</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">PRESENTACIÓN</th>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">PRECIO</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">RECETA</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">ESTADO</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">OPCIONES</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                @forelse ($products as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/70">
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->code }}
                            <div class="text-xs text-gray-500 dark:text-zinc-400">
                                {{ $item->barcode ?: 'Sin código de barras' }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            <div class="font-medium">{{ $item->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-zinc-400">
                                {{ $item->generic_name ?: '-' }}
                                @if($item->concentration)
                                    | {{ $item->concentration }}
                                @endif
                            </div>
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->category?->name ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->presentation?->name ?? '-' }}
                            <div class="text-xs text-gray-500 dark:text-zinc-400">
                                {{ $item->brand?->name ?? 'Sin marca' }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ number_format($item->sale_price, 2) }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $item->requires_prescription ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300' }}">
                                {{ $item->requires_prescription ? 'Sí' : 'No' }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $item->state ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $item->state ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex justify-center gap-3">
                                <a
                                    href="{{ route('products.edit', $item->id) }}" wire:navigate
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
                        <td colspan="8" class="px-4 py-4 text-center text-gray-500 dark:text-zinc-400">
                            No hay productos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $products->links() }}
    </div>
</div>