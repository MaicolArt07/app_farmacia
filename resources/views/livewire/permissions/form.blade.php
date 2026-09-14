<div class="mx-auto max-w-md p-6">
    <div class="mb-6">
        <a href="{{ route('permissions') }}" wire:navigate class="mb-2 inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
            <i class="bi bi-arrow-left"></i> Volver a permisos
        </a>
        <h1 class="flex items-center gap-2 text-xl font-semibold text-zinc-800 dark:text-zinc-100">
            <i class="bi bi-key-fill text-blue-600 dark:text-blue-400"></i>
            {{ $permissionId ? 'Editar Permiso' : 'Crear Permiso' }}
        </h1>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <form wire:submit.prevent="save" autocomplete="off">
            <div class="mb-4">
                <label class="mb-1 flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-200">
                    <i class="bi bi-tag-fill text-zinc-500 dark:text-zinc-400"></i>
                    Nombre del Permiso <span class="text-red-600">*</span>
                </label>

                <input
                    type="text"
                    wire:model.defer="name"
                    class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-zinc-800 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-400"
                />

                @error('name')
                    <span class="text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex justify-end gap-2">
                <button
                    type="button"
                    wire:click="cancel"
                    class="cursor-pointer rounded bg-gray-500 px-4 py-2 text-white transition hover:bg-gray-600 dark:bg-zinc-700 dark:hover:bg-zinc-600"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="cursor-pointer rounded bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                >
                    {{ $permissionId ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </form>
    </div>
</div>
