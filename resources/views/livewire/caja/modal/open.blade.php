<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl dark:bg-zinc-900">

        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-zinc-800">
            <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                Abrir caja
            </h2>

            <button wire:click="closeOpenModal" class="text-gray-500 hover:text-red-500 dark:text-zinc-400">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="space-y-4 px-6 py-5">
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-300">
                <i class="bi bi-calendar-event mr-1"></i> Se abrirá con la fecha de hoy: <span class="font-semibold">{{ now()->format('d/m/Y') }}</span>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Monto inicial <span class="text-red-600">*</span></label>
                <input type="number" step="0.01" wire:model="openForm.opening_amount"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-right dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('openForm.opening_amount')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Duración de la caja <span class="text-red-600">*</span></label>
                <select wire:model="openForm.validity_period"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="DAILY">Diaria (24 horas)</option>
                    <option value="WEEKLY">Semanal (7 días)</option>
                    <option value="MONTHLY">Mensual (30 días)</option>
                </select>
                <p class="mt-1 text-xs text-gray-400 dark:text-zinc-500">Pasado ese tiempo, el sistema te avisará que la caja está vencida para que la cierres o amplíes su vigencia.</p>
                @error('openForm.validity_period')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
            <button wire:click="closeOpenModal"
                class="rounded-lg border border-red-300 bg-white px-4 py-2 text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20 cursor-pointer">
                Cancelar
            </button>

            <button wire:click="saveOpen"
                class="rounded-lg border border-primary bg-white px-4 py-2 text-primary hover:bg-primary/10 dark:bg-zinc-800 dark:hover:bg-primary/20 cursor-pointer">
                Abrir
            </button>
        </div>
    </div>
</div>
