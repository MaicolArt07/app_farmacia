<div class="p-6">
    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <button type="button" wire:click="create"
            class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2 text-center text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600">
            <i class="bi bi-credit-card-2-front-fill mr-1"></i> Nuevo método de pago
        </button>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar método de pago..."
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none md:w-96 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-400"
        >
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-zinc-700 dark:text-zinc-200">NOMBRE</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">CÓDIGO SIN</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">ESTADO</th>
                    <th class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-200">OPCIONES</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                @forelse ($paymentMethods as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/70">
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                            {{ $item->nombre }}
                        </td>
                        <td class="px-4 py-3 text-center text-zinc-800 dark:text-zinc-100">
                            {{ $item->codigo_sin ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $item->estado ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $item->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-center gap-3">
                                <button type="button" wire:click="edit({{ $item->id }})"
                                    class="cursor-pointer text-yellow-500 transition hover:text-yellow-600 dark:text-yellow-400 dark:hover:text-yellow-300">
                                    <i class="bi bi-pencil-square text-lg"></i>
                                </button>

                                <button
                                    type="button"
                                    x-on:click.prevent="confirmAction({
                                        text: '¿Deseas {{ $item->estado ? 'desactivar' : 'activar' }} este registro?',
                                        confirmText: 'Sí, {{ $item->estado ? 'desactivar' : 'activar' }}',
                                    }).then((ok) => { if (ok) $wire.delete({{ $item->id }}) })"
                                    class="{{ $item->estado ? 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300' : 'text-gray-400 dark:text-zinc-500' }} cursor-pointer">
                                    <i class="bi {{ $item->estado ? 'bi-trash' : 'bi-arrow-clockwise' }} text-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-gray-500 dark:text-zinc-400">
                            No hay métodos de pago registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $paymentMethods->links() }}
    </div>

    <flux:modal wire:model="modalVisible" class="max-w-xl">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $form->id ? 'Editar método de pago' : 'Nuevo método de pago' }}
            </flux:heading>

            <div class="space-y-4">
                <flux:input
                    label="Nombre"
                    wire:model="form.nombre"
                />
                @error('form.nombre') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                <div>
                    <flux:input
                        type="number"
                        label="Código SIN (Opcional)"
                        wire:model="form.codigo_sin"
                    />
                    <p class="mt-1 text-xs text-gray-400 dark:text-zinc-500">Código oficial del catálogo "Tipo de Método de Pago" del SIN, para la facturación.</p>
                    @error('form.codigo_sin') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="danger" wire:click="cancel">
                    Cancelar
                </flux:button>

                <flux:button variant="primary" wire:click="save">
                    Guardar
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
