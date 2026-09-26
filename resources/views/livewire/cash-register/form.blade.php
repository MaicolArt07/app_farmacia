<div class="mx-auto max-w-md p-6">
    <div class="mb-6">
        <a href="{{ route('cash-registers') }}" wire:navigate class="mb-2 inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
            <i class="bi bi-arrow-left"></i> Volver a caja
        </a>
        <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
            Abrir caja
        </h1>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="space-y-4 px-6 py-5">
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-300">
                <i class="bi bi-calendar-event mr-1"></i> Se abrirá con la fecha de hoy: <span class="font-semibold">{{ now()->format('d/m/Y') }}</span>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Monto inicial <span class="text-red-600">*</span></label>
                <input type="number" step="0.01" wire:model="form.opening_amount"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-right dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.opening_amount')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Duración de la caja <span class="text-red-600">*</span></label>
                <select wire:model="form.validity_period"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="DAILY">Diaria (24 horas)</option>
                    <option value="WEEKLY">Semanal (7 días)</option>
                    <option value="MONTHLY">Mensual (30 días)</option>
                </select>
                <p class="mt-1 text-xs text-gray-400 dark:text-zinc-500">Pasado ese tiempo, el sistema te avisará que la caja está vencida para que la cierres o amplíes su vigencia.</p>
                @error('form.validity_period')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
            <button wire:click="cancel"
                class="rounded-lg border border-gray-300 px-4 py-2 text-zinc-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                Cancelar
            </button>

            <button wire:click="save"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                Abrir
            </button>
        </div>
    </div>
</div>
