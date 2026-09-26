<div class="mx-auto max-w-6xl p-6">
    <div class="mb-6">
        <a href="{{ route('users') }}" wire:navigate class="mb-2 inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
            <i class="bi bi-arrow-left"></i> Volver a usuarios
        </a>
        <h1 class="flex items-center gap-2 text-xl font-semibold text-zinc-800 dark:text-zinc-100">
            <i class="bi bi-person-circle text-blue-600 dark:text-blue-400"></i>
            {{ $form->id ? 'Editar Usuario' : 'Crear Usuario' }}
        </h1>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <form wire:submit.prevent="save" autocomplete="off">

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                <!-- ✅ COLUMNA IZQUIERDA: datos del usuario -->
                <div>
                    <!-- Persona vinculada -->
                    <div class="mb-4">
                        <label class="mb-1 flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-200">
                            <i class="bi bi-person-vcard-fill text-zinc-500"></i>
                            Persona vinculada <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span>
                        </label>

                        @if ($selectedPersonLabel)
                            <div class="flex items-center justify-between rounded border border-emerald-300 bg-emerald-50 px-3 py-2 dark:border-emerald-800 dark:bg-emerald-900/20">
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
                                    class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-zinc-800 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >

                                @if (!empty($personResults))
                                    <div class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded border border-gray-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
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
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Nombre -->
                    <div class="mb-4">
                        <label class="mb-1 flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-200">
                            <i class="bi bi-person-fill text-zinc-500"></i>
                            Nombre <span class="text-red-600">*</span>
                        </label>

                        <input
                            type="text"
                            wire:model.defer="form.name"
                            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-zinc-800 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />

                        @error('form.name')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label class="mb-1 flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-200">
                            <i class="bi bi-envelope-fill text-zinc-500"></i>
                            Correo <span class="text-red-600">*</span>
                        </label>

                        <input
                            type="email"
                            wire:model.defer="form.email"
                            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-zinc-800 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />

                        @error('form.email')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label class="mb-1 flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-200">
                            <i class="bi bi-lock-fill text-zinc-500"></i>
                            Contraseña
                            @if ($form->id)
                                <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional, dejar en blanco para no cambiarla)</span>
                            @else
                                <span class="text-red-600">*</span>
                            @endif
                        </label>

                        <input
                            type="password"
                            wire:model.defer="form.password"
                            autocomplete="new-password"
                            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-zinc-800 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />

                        @error('form.password')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Confirmar password -->
                    <div>
                        <label class="mb-1 flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-200">
                            <i class="bi bi-shield-lock-fill text-zinc-500"></i>
                            Confirmar contraseña
                            @unless ($form->id)
                                <span class="text-red-600">*</span>
                            @endunless
                        </label>

                        <input
                            type="password"
                            wire:model.defer="form.password_confirmation"
                            autocomplete="new-password"
                            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-zinc-800 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                    </div>
                </div>

                <!-- ✅ COLUMNA DERECHA: permisos -->
                <div>
                    <label class="mb-1 flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-200">
                        <i class="bi bi-shield-lock-fill text-zinc-500"></i>
                        Permisos <span class="text-red-600">*</span>
                    </label>

                    <div class="max-h-[28rem] space-y-2 overflow-y-auto rounded border border-gray-200 p-3 dark:border-zinc-700 dark:bg-zinc-800/40 lg:max-h-[32rem]">
                        @foreach ($permissionGroups as $group => $permissions)
                            @php
                                $groupNames = collect($permissions)->pluck('name');
                                $allChecked = $groupNames->diff($form->selectedPermissions)->isEmpty();
                            @endphp
                            <details class="group rounded border border-gray-100 dark:border-zinc-700">
                                <summary class="flex cursor-pointer items-center justify-between px-2 py-1.5 text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                                    <span class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            wire:click.stop="toggleGroup('{{ $group }}')"
                                            @checked($allChecked)
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800"
                                            title="Marcar todo el grupo"
                                        >
                                        <span>{{ $group }}</span>
                                    </span>
                                    <i class="bi bi-chevron-down text-xs transition-transform group-open:rotate-180"></i>
                                </summary>

                                <div class="grid grid-cols-1 gap-1 px-2 pb-2 sm:grid-cols-2">
                                    @foreach ($permissions as $permission)
                                        <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                                            <input
                                                type="checkbox"
                                                wire:model.defer="form.selectedPermissions"
                                                value="{{ $permission['name'] }}"
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800"
                                            >
                                            {{ $permission['name'] }}
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </div>

                    @error('form.selectedPermissions')
                        <span class="text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Botones -->
            <div class="mt-6 flex justify-end gap-2 border-t border-gray-200 pt-4 dark:border-zinc-800">
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
                    {{ $form->id ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </form>
    </div>
</div>
