<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl dark:bg-zinc-900">

        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-zinc-800">
            <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                Detalle de caja — {{ $detailCash->fecha_apertura?->format('d/m/Y') }}
            </h2>

            <button wire:click="closeDetailModal" class="text-gray-500 hover:text-red-500 dark:text-zinc-400">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="max-h-[70vh] overflow-y-auto px-6 py-5">
            @if($detailCash->status === 'CLOSED')
                @if($detailCash->monto_qr_verificado !== null)
                    @php $difQr = (float) $detailCash->diferencia_qr; @endphp
                    <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">1) Verificación QR</div>
                    <div class="mb-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">QR en la cuenta (sistema: {{ number_format($detailCash->monto_qr_esperado, 2) }})</div>
                            <div class="font-semibold text-zinc-800 dark:text-zinc-100">{{ number_format($detailCash->monto_qr_verificado, 2) }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Diferencia QR — {{ $difQr == 0 ? 'cuadra' : ($difQr > 0 ? 'sobrante' : 'faltante') }}</div>
                            <div class="font-semibold {{ $difQr == 0 ? 'text-emerald-600 dark:text-emerald-400' : ($difQr > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400') }}">
                                {{ $difQr > 0 ? '+' : '' }}{{ number_format($difQr, 2) }}
                            </div>
                        </div>
                    </div>
                    <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">2) Efectivo (cortes)</div>
                @endif
                <div class="mb-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Monto contado</div>
                        <div class="font-semibold text-zinc-800 dark:text-zinc-100">{{ number_format($detailCash->monto_cierre, 2) }}</div>
                    </div>
                    @php $dif = (float) $detailCash->diferencia; @endphp
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Diferencia — {{ $dif == 0 ? 'cuadra' : ($dif > 0 ? 'sobrante' : 'faltante') }}</div>
                        <div class="font-semibold {{ $dif == 0 ? 'text-emerald-600 dark:text-emerald-400' : ($dif > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400') }}">
                            {{ $dif > 0 ? '+' : '' }}{{ number_format($dif, 2) }}
                        </div>
                    </div>
                </div>

                @if($detailCash->closed_at || $detailCash->observacion)
                    <div class="mb-4 rounded-lg border border-gray-200 bg-white p-3 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                        @if($detailCash->closed_at)
                            <div class="text-zinc-600 dark:text-zinc-300">Cerrada el <span class="font-semibold">{{ \Carbon\Carbon::parse($detailCash->closed_at)->format('d/m/Y H:i') }}</span></div>
                        @endif
                        @if($detailCash->observacion)
                            <div class="mt-1 text-zinc-700 dark:text-zinc-200"><span class="font-semibold">Observación:</span> {{ $detailCash->observacion }}</div>
                        @endif
                    </div>
                @endif
            @endif

            @include('livewire.caja.partials.summary', ['cash' => $detailCash])
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
            <button wire:click="closeDetailModal"
                class="rounded-lg border border-gray-300 px-4 py-2 text-zinc-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                Cerrar
            </button>
        </div>
    </div>
</div>
