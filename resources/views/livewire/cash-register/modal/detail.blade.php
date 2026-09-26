<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl dark:bg-zinc-900">

        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-zinc-800">
            <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                Detalle de caja — {{ $detailCash->opening_date?->format('d/m/Y') }}
            </h2>

            <button wire:click="closeDetailModal" class="text-gray-500 hover:text-red-500 dark:text-zinc-400">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="max-h-[70vh] overflow-y-auto px-6 py-5">
            @if($detailCash->status === 'CLOSED')
                <div class="mb-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Monto contado</div>
                        <div class="font-semibold text-zinc-800 dark:text-zinc-100">{{ number_format($detailCash->closing_amount, 2) }}</div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Diferencia</div>
                        <div class="font-semibold {{ $detailCash->difference == 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ number_format($detailCash->difference, 2) }}
                        </div>
                    </div>
                </div>
            @endif

            @include('livewire.cash-register.partials.summary', ['cash' => $detailCash])
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
            <button wire:click="closeDetailModal"
                class="rounded-lg border border-gray-300 px-4 py-2 text-zinc-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                Cerrar
            </button>
        </div>
    </div>
</div>
