<div class="flex flex-col bg-zinc-200 p-4 dark:bg-zinc-950">

    @if (session()->has('message'))
        <div class="mb-3 rounded border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
        <button type="button" wire:click="create"
            class="flex items-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
            <i class="bi bi-person-plus-fill text-primary"></i> Nueva persona
        </button>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar persona..."
            class="w-full rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-primary focus:outline-none md:w-80 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500"
        >
    </div>

    <div class="overflow-auto rounded border border-zinc-300 dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Nombre</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Apellido</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">CI</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Teléfono</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Opciones</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($persons as $item)
                    <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->nombre }}
                        </td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->apellido }}
                        </td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->ci }}
                        </td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $item->telefono }}
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
                            No hay personas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $persons->links() }}
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
                    {{ $form->id ? 'Editar persona' : 'Registrar persona' }}
                </h2>
                <button wire:click="cancel" class="text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 gap-4 px-4 py-5 md:grid-cols-2 xl:grid-cols-3">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Nombre</label>
                    <input type="text" wire:model="form.nombre" placeholder="Nombre"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.nombre')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Apellido</label>
                    <input type="text" wire:model="form.apellido" placeholder="Apellido"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.apellido')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">CI</label>
                    <input type="text" wire:model="form.ci" placeholder="CI"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.ci')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Teléfono</label>
                    <input type="text" wire:model="form.telefono" placeholder="Teléfono"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('form.telefono')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">País <span class="text-red-600">*</span></label>

                    <x-search-select
                        model="form.id_country"
                        :value="$form->id_country"
                        placeholder="Seleccione un país"
                        :options="$countries->map(fn ($c) => ['value' => $c->id, 'label' => $c->nombre])->values()"
                    />

                    @error('form.id_country')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1 md:col-span-2 xl:col-span-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Dirección</label>
                    <textarea wire:model="form.direccion" rows="1" placeholder="Dirección"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                    @error('form.direccion')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-zinc-300 bg-zinc-100 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <button wire:click="cancel" class="rounded border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20 cursor-pointer">
                    Cancelar
                </button>
                <button wire:click="save" class="rounded border border-primary bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:brightness-110 cursor-pointer dark:border-primary dark:bg-primary dark:text-white dark:hover:brightness-110">
                    <i class="bi bi-check-lg text-white"></i> Guardar
                </button>
            </div>
        </div>
    </div>

</div>
