@php $noteCash = \App\Models\Caja::with('user')->find($noteCashId); @endphp

<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl dark:bg-zinc-900">

        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-zinc-800">
            <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                <i class="bi bi-journal-text mr-1"></i>Observación de la caja
            </h2>
            <button wire:click="closeNoteModal" class="text-gray-500 hover:text-red-500 dark:text-zinc-400">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="space-y-3 px-6 py-5">
            @if($noteCash)
                @php $dif = $noteCash->diferencia !== null ? (float) $noteCash->diferencia : null; @endphp
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-200">
                    <span class="font-semibold">{{ $noteCash->user?->name }}</span>
                    — {{ \Carbon\Carbon::parse($noteCash->fecha_apertura)->format('d/m/Y') }}
                    {{ $noteCash->created_at?->format('H:i') }}
                    @if($dif !== null)
                        · Efectivo:
                        <span class="font-semibold {{ $dif == 0 ? 'text-emerald-600 dark:text-emerald-400' : ($dif > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400') }}">
                            {{ $dif > 0 ? '+' : '' }}{{ number_format($dif, 2) }} {{ $dif == 0 ? '(cuadra)' : ($dif > 0 ? '(sobrante)' : '(faltante)') }}
                        </span>
                    @endif
                    @if($noteCash->diferencia_qr !== null && (float) $noteCash->diferencia_qr != 0)
                        · QR: <span class="font-semibold">{{ (float) $noteCash->diferencia_qr > 0 ? '+' : '' }}{{ number_format($noteCash->diferencia_qr, 2) }}</span>
                    @endif
                </div>
            @endif

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    ¿Qué pasó en esta caja?
                </label>
                <textarea wire:model="noteText" rows="7" maxlength="5000"
                    placeholder="Ej: El sobrante de 33 Bs es porque una venta QR se registró como efectivo..."
                    class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-zinc-800 dark:text-zinc-100 {{ $errors->has('noteText') ? 'border-red-500' : 'border-gray-300 dark:border-zinc-700' }}"></textarea>
                @error('noteText') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Solo la ve y la edita el administrador. Déjela vacía para borrarla.</p>
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
            <button wire:click="closeNoteModal"
                class="rounded-lg border border-red-300 bg-white px-4 py-2 text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20 cursor-pointer">
                Cancelar
            </button>
            <button wire:click="saveNote"
                class="rounded-lg border border-primary bg-white px-4 py-2 text-primary hover:bg-primary/10 dark:bg-zinc-800 dark:hover:bg-primary/20 cursor-pointer">
                Guardar observación
            </button>
        </div>
    </div>
</div>
