<div class="mx-auto max-w-4xl p-6">
    <div class="mb-6">
        <a href="{{ route('person') }}" wire:navigate class="mb-2 inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
            <i class="bi bi-arrow-left"></i> Volver a personas
        </a>
        <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
            {{ $form->id ? 'Editar Persona' : 'Registrar Persona' }}
        </h1>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">

            <!-- Nombre -->
            <div class="space-y-1">
                <label class="font-semibold text-zinc-700 dark:text-zinc-200">Nombre <span class="text-red-600">*</span></label>

                <input type="text" wire:model="form.name"
                       class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-400"
                       placeholder="Nombre">

                @error('form.name')
                    <span class="text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <!-- Apellido -->
            <div class="space-y-1">
                <label class="font-semibold text-zinc-700 dark:text-zinc-200">Apellido <span class="text-red-600">*</span></label>

                <input type="text" wire:model="form.lastname"
                       class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-400"
                       placeholder="Apellido">

                @error('form.lastname')
                    <span class="text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <!-- CI -->
            <div class="space-y-1">
                <label class="font-semibold text-zinc-700 dark:text-zinc-200">CI <span class="text-red-600">*</span></label>

                <input type="text" wire:model="form.ci"
                       class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-400"
                       placeholder="CI">

                @error('form.ci')
                    <span class="text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <!-- Telefono -->
            <div class="space-y-1">
                <label class="font-semibold text-zinc-700 dark:text-zinc-200">Telefono <span class="text-red-600">*</span></label>

                <input type="text" wire:model="form.phone"
                       class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-400"
                       placeholder="Telefono">

                @error('form.phone')
                    <span class="text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <!-- Pais -->
            <div class="space-y-1">
                <label class="font-semibold text-zinc-700 dark:text-zinc-200">Pais <span class="text-red-600">*</span></label>

                <x-search-select
                    model="form.id_country"
                    :value="$form->id_country"
                    placeholder="Seleccione un pais"
                    :options="$countries->map(fn ($c) => ['value' => $c->id, 'label' => $c->name])->values()"
                />

                @error('form.id_country')
                    <span class="text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <!-- Direccion -->
            <div class="space-y-1 md:col-span-2 xl:col-span-1">
                <label class="font-semibold text-zinc-700 dark:text-zinc-200">Direccion <span class="text-red-600">*</span></label>

                <textarea wire:model="form.address" rows="1"
                          class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-400"
                          placeholder="Direccion"></textarea>

                @error('form.address')
                    <span class="text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

        </div>

        <div class="mt-6 flex justify-end gap-3">
            <button wire:click="cancel"
                    class="rounded bg-red-600 px-4 py-2 text-white transition hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 cursor-pointer">
                Cancelar
            </button>

            <button wire:click="save"
                class="rounded bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 cursor-pointer">
                Guardar
            </button>
        </div>
    </div>
</div>
