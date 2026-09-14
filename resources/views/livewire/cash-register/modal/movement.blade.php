<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl dark:bg-zinc-900">

        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-zinc-800">
            <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                Registrar movimiento
            </h2>

            <button wire:click="closeMovementModal" class="text-gray-500 hover:text-red-500 dark:text-zinc-400">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="space-y-4 px-6 py-5">
            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Tipo <span class="text-red-600">*</span></label>
                <select wire:model="movement_type"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="INCOME">Ingreso</option>
                    <option value="EXPENSE">Egreso</option>
                </select>
                @error('movement_type')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Concepto <span class="text-red-600">*</span></label>
                <input type="text" wire:model="movement_concept"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('movement_concept')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Monto <span class="text-red-600">*</span></label>
                <input type="number" step="0.01" wire:model="movement_amount"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-right dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('movement_amount')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Observación <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <textarea wire:model="movement_observation" rows="2"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                @error('movement_observation')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
            <button wire:click="closeMovementModal"
                class="rounded-lg border border-gray-300 px-4 py-2 text-zinc-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                Cancelar
            </button>

            <button wire:click="saveMovement"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                Guardar
            </button>
        </div>
    </div>
</div>
