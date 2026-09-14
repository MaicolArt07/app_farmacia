<div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity duration-300">
    <div class="relative w-full max-w-md rounded-lg border border-gray-200 bg-white p-6 shadow-lg animate-[modalDrop_0.30s_ease-out] dark:border-zinc-700 dark:bg-zinc-900">

        <h2 class="mb-4 flex items-center gap-2 text-xl font-semibold text-zinc-800 dark:text-zinc-100">
            <i class="bi bi-shield-lock-fill text-blue-600 dark:text-blue-400"></i>
            {{ $roleId ? 'Editar Rol' : 'Crear Rol' }}
        </h2>

        <form wire:submit.prevent="save" autocomplete="off">
            <div class="mb-4">
                <label class="mb-1 flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-200">
                    <i class="bi bi-tag-fill text-zinc-500 dark:text-zinc-400"></i>
                    Nombre del Rol
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

            <div class="mb-4">
                <label class="mb-1 flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-200">
                    <i class="bi bi-key-fill text-zinc-500 dark:text-zinc-400"></i>
                    Permisos
                </label>

                <div class="grid max-h-48 grid-cols-2 gap-2 overflow-y-auto rounded border border-gray-300 p-2 dark:border-zinc-700 dark:bg-zinc-800/40">
                    @foreach ($permissions as $perm)
                        <label class="inline-flex items-center space-x-2 text-zinc-700 dark:text-zinc-200">
                            <input
                                type="checkbox"
                                wire:model.defer="selectedPermissions"
                                value="{{ $perm->name }}"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-blue-400"
                            >
                            <span>{{ $perm->name }}</span>
                        </label>
                    @endforeach
                </div>

                @error('selectedPermissions')
                    <span class="text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex justify-end space-x-2">
                <button
                    type="button"
                    wire:click="$set('modalVisible', false)"
                    class="cursor-pointer rounded bg-gray-500 px-4 py-2 text-white transition hover:bg-gray-600 dark:bg-zinc-700 dark:hover:bg-zinc-600"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="cursor-pointer rounded bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                >
                    {{ $roleId ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </form>

        <button
            wire:click="$set('modalVisible', false)"
            class="absolute right-3 top-3 cursor-pointer text-xl text-gray-600 transition hover:text-gray-900 dark:text-zinc-400 dark:hover:text-zinc-100"
        >
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</div>