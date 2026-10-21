<div class="flex flex-col bg-zinc-200 p-4 dark:bg-zinc-950">
    @if (session()->has('message'))
        <div class="mb-3 rounded border border-emerald-300 bg-emerald-100 p-3 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
        <button type="button" wire:click="create"
            class="flex items-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
            <i class="bi bi-plus-lg text-primary"></i> Nuevo permiso
        </button>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar permiso..."
            class="w-full rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-primary focus:outline-none md:w-80 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500"
        >
    </div>

    <div class="overflow-auto rounded border border-zinc-300 dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Nombre del permiso</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">Opciones</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($permissions as $permission)
                    <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <td class="px-3 py-1.5 capitalize text-zinc-800 dark:text-zinc-100">
                            {{ $permission->name }}
                        </td>

                        <td class="px-3 py-1.5">
                            <div class="flex justify-center gap-3">
                                <button type="button" wire:click="edit({{ $permission->id }})"
                                    class="cursor-pointer text-zinc-500 transition hover:text-primary dark:text-zinc-400">
                                    <i class="bi bi-pencil-square text-base"></i>
                                </button>

                                <button
                                    type="button"
                                    x-on:click.prevent="confirmAction({
                                        text: '¿Eliminar este permiso?',
                                        confirmText: 'Sí, eliminar',
                                    }).then((ok) => { if (ok) $wire.delete({{ $permission->id }}) })"
                                    class="cursor-pointer text-zinc-500 transition hover:text-red-600 dark:text-zinc-400">
                                    <i class="bi bi-trash text-base"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">
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

    <flux:modal wire:model="modalVisible" class="max-w-md">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $form->permissionId ? 'Editar Permiso' : 'Crear Permiso' }}
            </flux:heading>

            <div>
                <flux:input
                    label="Nombre del Permiso"
                    wire:model.defer="form.name"
                />
                @error('form.name')
                    <span class="text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex justify-end gap-3 border-t border-zinc-300 pt-4 dark:border-zinc-700">
                <button wire:click="cancel" type="button" class="rounded border border-zinc-400 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 cursor-pointer">
                    Cancelar
                </button>

                <button wire:click="save" type="button" class="rounded border border-zinc-400 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700">
                    <i class="bi bi-check-lg text-primary"></i> {{ $form->permissionId ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </div>
    </flux:modal>

</div>
