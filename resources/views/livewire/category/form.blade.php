<div class="mx-auto max-w-xl p-6">
    <div class="mb-6">
        <a href="{{ route('categories') }}" wire:navigate class="mb-2 inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
            <i class="bi bi-arrow-left"></i> Volver a categorías
        </a>
        <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
            {{ $form->id ? 'Editar categoría' : 'Nueva categoría' }}
        </h1>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="space-y-4 px-6 py-5">
            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Nombre <span class="text-red-600">*</span></label>
                <input
                    type="text"
                    wire:model="form.name"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                >
                @error('form.name')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Descripción <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <textarea
                    wire:model="form.description"
                    rows="3"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                ></textarea>
                @error('form.description')
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
