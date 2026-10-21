<div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-zinc-900/30" wire:click="$set('modalVisible', false)"></div>

    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="relative flex w-full max-w-md flex-col rounded border border-zinc-300 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900 max-h-[90vh]">

        <div class="flex items-center justify-between border-b border-zinc-300 bg-zinc-200 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
            <h2 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                {{ $roleId ? 'Editar Rol' : 'Crear Rol' }}
            </h2>
            <button wire:click="$set('modalVisible', false)" class="text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form wire:submit.prevent="save" autocomplete="off" class="flex flex-1 flex-col overflow-hidden">
            <div class="space-y-5 overflow-y-auto px-4 py-5">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Nombre del rol</label>
                    <input
                        type="text"
                        wire:model.defer="name"
                        class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                    @error('name')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Permisos</label>

                    <div class="grid max-h-56 grid-cols-2 gap-2 overflow-y-auto rounded border border-zinc-400 bg-zinc-100 p-2 dark:border-zinc-600 dark:bg-zinc-800">
                        @foreach ($permissions as $perm)
                            <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                                <input
                                    type="checkbox"
                                    wire:model.defer="selectedPermissions"
                                    value="{{ $perm->name }}"
                                    class="rounded border-zinc-400 accent-primary dark:border-zinc-600"
                                >
                                <span>{{ $perm->name }}</span>
                            </label>
                        @endforeach
                    </div>

                    @error('selectedPermissions')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-zinc-300 bg-zinc-100 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <button type="button" wire:click="$set('modalVisible', false)" class="rounded border border-zinc-400 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="rounded border border-zinc-400 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-100 cursor-pointer dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700">
                    <i class="bi bi-check-lg text-primary"></i> {{ $roleId ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </form>
    </div>
</div>
