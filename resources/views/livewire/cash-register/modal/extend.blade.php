<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl dark:bg-zinc-900">

        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-zinc-800">
            <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                Ampliar vigencia de caja
            </h2>

            <button wire:click="closeExtendModal" class="text-gray-500 hover:text-red-500 dark:text-zinc-400">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="space-y-4 px-6 py-5">
            @if($extendingCash)
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Fecha de inicio</div>
                        <div class="mt-1 font-semibold text-zinc-800 dark:text-zinc-100">
                            {{ $extendingCash->opening_date?->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Vence actualmente</div>
                        <div class="mt-1 font-semibold text-zinc-800 dark:text-zinc-100">
                            {{ $extendingCash->expires_at?->format('d/m/Y H:i') ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                    Última ampliación: <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $extendingCash->last_extended_at?->format('d/m/Y H:i') ?? 'Nunca' }}</span>
                </div>
            @endif

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Nueva fecha final <span class="text-red-600">*</span></label>
                <input type="datetime-local" wire:model="extendUntil"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('extendUntil')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror

                <div class="mt-2 flex flex-wrap gap-2">
                    <button type="button" wire:click="setExtendPreset('DAILY')"
                        class="rounded-full border border-gray-300 px-3 py-1 text-xs text-zinc-600 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        +1 día
                    </button>
                    <button type="button" wire:click="setExtendPreset('WEEKLY')"
                        class="rounded-full border border-gray-300 px-3 py-1 text-xs text-zinc-600 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        +1 semana
                    </button>
                    <button type="button" wire:click="setExtendPreset('MONTHLY')"
                        class="rounded-full border border-gray-300 px-3 py-1 text-xs text-zinc-600 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        +1 mes
                    </button>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
            <button wire:click="closeExtendModal"
                class="rounded-lg border border-gray-300 px-4 py-2 text-zinc-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                Cancelar
            </button>

            <button wire:click="confirmExtend"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                Ampliar vigencia
            </button>
        </div>
    </div>
</div>
