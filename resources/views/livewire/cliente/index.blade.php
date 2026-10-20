<div class="p-6">
    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <button type="button" wire:click="create"
            class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2 text-center text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600">
            <i class="bi bi-person-plus-fill mr-1"></i> Nuevo cliente
        </button>

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
                            {{ $item->person?->nombre }} {{ $item->person?->apellido }}
                            <div class="text-xs text-gray-500 dark:text-zinc-400">
                                CI: {{ $item->person?->ci ?? '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">{{ $item->code }}</td>
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">{{ strtoupper($item->tipo) }}</td>
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">{{ $item->nit ?: '-' }}</td>
                        <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">{{ $item->email ?: '-' }}</td>
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

    <flux:modal wire:model="modalVisible" class="max-w-2xl">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $form->id ? 'Editar cliente' : 'Nuevo cliente' }}
            </flux:heading>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Buscar persona <span class="text-red-600">*</span></label>

                    @if ($selectedPersonLabel)
                        <div class="flex items-center justify-between rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 dark:border-emerald-800 dark:bg-emerald-900/20">
                            <span class="text-sm text-emerald-700 dark:text-emerald-300">
                                {{ $selectedPersonLabel }}
                            </span>
                            <button wire:click="clearPerson" type="button" class="text-red-500 hover:text-red-700">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    @else
                        <div class="relative">
                            <input
                                type="text"
                                wire:model.live="personSearch"
                                placeholder="Buscar por nombre, apellido o CI..."
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-zinc-800 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >

                            @if (!empty($personResults))
                                <div class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                                    @foreach ($personResults as $person)
                                        <button
                                            type="button"
                                            wire:click="selectPerson({{ $person['id'] }})"
                                            class="block w-full border-b border-gray-100 px-3 py-2 text-left text-sm hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-700"
                                        >
                                            {{ $person['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    @error('form.id_person')
                        <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <flux:input
                    label="Código"
                    wire:model="form.code"
                />
                @error('form.code')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror

                <flux:select label="Tipo" wire:model="form.tipo">
                    <flux:select.option value="natural">Natural</flux:select.option>
                    <flux:select.option value="company">Empresa</flux:select.option>
                </flux:select>
                @error('form.tipo')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror

                <flux:input
                    label="NIT (Opcional)"
                    wire:model="form.nit"
                />
                @error('form.nit')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror

                <flux:input
                    type="email"
                    label="Email (Opcional)"
                    wire:model="form.email"
                />
                @error('form.email')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
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
