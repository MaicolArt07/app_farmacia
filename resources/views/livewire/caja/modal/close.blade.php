<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl dark:bg-zinc-900">

        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-zinc-800">
            <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                Cerrar caja
            </h2>

            <button wire:click="closeCloseCashModal" class="text-gray-500 hover:text-red-500 dark:text-zinc-400">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="max-h-[70vh] space-y-4 overflow-y-auto px-6 py-5">
            @if($closingCash)
                @include('livewire.caja.partials.summary', ['cash' => $closingCash])
            @endif

            <div class="border-t border-gray-200 pt-4 dark:border-zinc-700">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Monto contado <span class="text-red-600">*</span></label>
                <input type="number" step="0.01" wire:model.live="closeForm.closing_amount"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-right dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('closeForm.closing_amount')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-base dark:border-zinc-700 dark:bg-zinc-800/50">
                <span class="font-semibold text-zinc-700 dark:text-zinc-200">Diferencia proyectada</span>
                <span class="font-bold {{ $projectedDifference == 0 ? 'text-emerald-600 dark:text-emerald-400' : ($projectedDifference < 0 ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400') }}">
                    {{ number_format($projectedDifference, 2) }}
                </span>
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
            <button wire:click="closeCloseCashModal"
                class="rounded-lg border border-red-300 bg-white px-4 py-2 text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20 cursor-pointer">
                Cancelar
            </button>

            <button wire:click="closeCashRegister"
                class="rounded-lg border border-primary bg-white px-4 py-2 text-primary hover:bg-primary/10 dark:bg-zinc-800 dark:hover:bg-primary/20 cursor-pointer">
                Cerrar caja
            </button>
        </div>
    </div>
</div>
