<div class="mx-auto max-w-2xl p-6">
    <div class="mb-6">
        <a href="{{ route('clients') }}" wire:navigate class="mb-2 inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
            <i class="bi bi-arrow-left"></i> Volver a clientes
        </a>
        <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
            {{ $form->id ? 'Editar cliente' : 'Nuevo cliente' }}
        </h1>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid grid-cols-1 gap-4 px-6 py-5 md:grid-cols-2">

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

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Código <span class="text-red-600">*</span></label>
                <input
                    type="text"
                    wire:model="form.code"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                >
                @error('form.code')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Tipo <span class="text-red-600">*</span></label>
                <select
                    wire:model="form.type"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                >
                    <option value="natural">Natural</option>
                    <option value="company">Empresa</option>
                </select>
                @error('form.type')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">NIT <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input
                    type="text"
                    wire:model="form.nit"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                >
                @error('form.nit')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Email <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input
                    type="email"
                    wire:model="form.email"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                >
                @error('form.email')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
            <button
                wire:click="cancel"
                class="rounded-lg border border-gray-300 px-4 py-2 text-zinc-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
            >
                Cancelar
            </button>

            <button
                wire:click="save"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
            >
                Guardar
            </button>
        </div>
    </div>
</div>
