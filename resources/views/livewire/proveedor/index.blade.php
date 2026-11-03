<div class="flex flex-col bg-zinc-200 p-4 dark:bg-zinc-950">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
        <button type="button" wire:click="create"
            class="flex items-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
            <i class="bi bi-truck text-primary"></i> Nuevo proveedor
        </button>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar proveedor..."
            class="w-full rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-primary focus:outline-none md:w-80 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500"
        >
    </div>

    <div class="hidden overflow-auto rounded border border-zinc-300 md:block dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Persona</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Razón social</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Cargo</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">NIT</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Email</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Estado</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Opciones</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($suppliers as $item)
                    <tr wire:click="view({{ $item->id }})" class="cursor-pointer border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->person?->nombre }} {{ $item->person?->apellido }}
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                CI: {{ $item->person?->ci ?? '-' }}
                            </div>
                        </td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $item->razon_social }}</td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $item->cargo_contacto ?: '-' }}</td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $item->nit ?: '-' }}</td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">{{ $item->email ?: '-' }}</td>
                        <td class="px-3 py-1.5 text-center">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $item->estado ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $item->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-3 py-1.5" onclick="event.stopPropagation()">
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
                        <td colspan="7" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            No hay proveedores registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex flex-col gap-2 md:hidden">
        @forelse ($suppliers as $item)
            <div
                x-data="{ open: false }"
                wire:click="view({{ $item->id }})"
                class="cursor-pointer rounded border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div class="flex items-center gap-2">
                    <button type="button" x-on:click.stop="open = !open"
                        class="flex h-6 w-6 shrink-0 cursor-pointer items-center justify-center rounded-full border border-primary text-primary transition hover:bg-primary hover:text-white">
                        <i class="bi text-xs" :class="open ? 'bi-dash' : 'bi-plus'"></i>
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $item->razon_social }}</p>
                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $item->person?->nombre }} {{ $item->person?->apellido }}</p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3" onclick="event.stopPropagation()">
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
                </div>

                <div x-show="open" x-collapse x-cloak onclick="event.stopPropagation()" class="mt-2 grid grid-cols-2 gap-2 border-t border-zinc-200 pt-2 pl-8 dark:border-zinc-800">
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Cargo</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->cargo_contacto ?: '-' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">NIT</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->nit ?: '-' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Email</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item->email ?: '-' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Estado</span>
                        <p>
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $item->estado ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $item->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded border border-zinc-300 bg-white px-3 py-4 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                No hay proveedores registrados.
            </div>
        @endforelse
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $suppliers->links() }}
    </div>

    <div x-data="{ open: false }" x-init="$watch('$wire.modalVisible', value => open = value)" x-show="$wire.modalVisible" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-zinc-900/30" wire:click="cancel"></div>

        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="relative flex w-full max-w-2xl flex-col rounded border border-zinc-300 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">

            <div class="flex items-center justify-between border-b border-zinc-300 bg-zinc-200 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ $form->id ? 'Editar proveedor' : 'Nuevo proveedor' }}
                </h2>
                <button wire:click="cancel" class="text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 gap-4 px-4 py-5 md:grid-cols-2">

                <div class="space-y-1 md:col-span-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Buscar persona <span class="text-red-600">*</span></label>

                    @if ($selectedPersonLabel)
                        <div class="flex items-center justify-between rounded border border-emerald-300 bg-emerald-50 px-3 py-2 dark:border-emerald-800 dark:bg-emerald-900/20">
                            <span class="text-sm text-emerald-700 dark:text-emerald-300">
                                {{ $selectedPersonLabel }}
                            </span>
                            <button wire:click="clearPerson" type="button" class="text-red-500 hover:text-red-700 cursor-pointer">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    @else
                        <div class="relative">
                            <input
                                type="text"
                                wire:model.live="personSearch"
                                placeholder="Buscar por nombre, apellido o CI..."
                                class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                            >

                            @if (!empty($personResults))
                                <div class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded border border-zinc-300 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                                    @foreach ($personResults as $person)
                                        <button
                                            type="button"
                                            wire:click="selectPerson({{ $person['id'] }})"
                                            class="block w-full border-b border-zinc-200 px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-700 cursor-pointer"
                                        >
                                            {{ $person['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    @error('form.id_persona')
                        <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Razón social</label>
                    <input type="text" wire:model="form.razon_social"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.razon_social')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Cargo de contacto (Opcional)</label>
                    <input type="text" wire:model="form.cargo_contacto"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.cargo_contacto')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">NIT (Opcional)</label>
                    <input type="text" wire:model="form.nit"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.nit')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Email (Opcional)</label>
                    <input type="email" wire:model="form.email"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.email')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
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

    <div x-data="{ open: false }" x-init="$watch('$wire.viewingId', value => open = !!value)" x-show="!!$wire.viewingId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-zinc-900/30" wire:click="closeView"></div>

        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="relative flex w-full max-w-lg flex-col rounded border border-zinc-300 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">

            @if ($viewing)
                <div class="flex items-center justify-between border-b border-zinc-300 bg-zinc-200 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Detalle de proveedor</h2>
                    <button wire:click="closeView" class="text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="space-y-4 px-4 py-5">
                    <div class="space-y-1">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Contacto (persona)</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">
                            {{ trim(($viewing->person?->nombre ?? '') . ' ' . ($viewing->person?->apellido ?? '')) ?: '-' }}
                            @if ($viewing->person)
                                - CI: {{ $viewing->person->ci ?? '-' }} - Tel: {{ $viewing->person->telefono ?? '-' }}
                            @endif
                        </p>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Razón social</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->razon_social }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Cargo de contacto</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->cargo_contacto ?: '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">NIT</span>
                            <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->nit ?: '-' }}</p>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Email</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">{{ $viewing->email ?: '-' }}</p>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Estado</span>
                        <p class="text-sm text-zinc-800 dark:text-zinc-100">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $viewing->estado ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $viewing->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </p>
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
