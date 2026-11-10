@php
    // Cierre en dos verificaciones: 1) QR (cuenta bancaria)  2) Efectivo (cortes).
    $b = $closingCash?->cashBreakdown();
    $qrSales = $closingCash
        // QR puro + la parte QR de las ventas Mixto.
        ? $closingCash->sales->where('status', 'ACTIVE')->filter(fn ($s) => $s->qrPart() > 0)
        : collect();
    $qrTyped = $closeForm->qr_amount !== '' && is_numeric($closeForm->qr_amount);
    $difClass = fn ($d) => $d == 0 ? 'text-emerald-600 dark:text-emerald-400' : ($d < 0 ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400');
    $difText = fn ($d) => $d == 0 ? 'cuadra' : ($d < 0 ? 'faltante' : 'sobrante');
@endphp

<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl dark:bg-zinc-900">

        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-zinc-800">
            <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                Cerrar caja
                @if($closingCash)
                    <span class="text-sm font-normal text-zinc-500 dark:text-zinc-400">— {{ $closingCash->user?->name }}</span>
                @endif
            </h2>

            <button wire:click="closeCloseCashModal" class="text-gray-500 hover:text-red-500 dark:text-zinc-400">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Pasos --}}
        <div class="flex items-center gap-2 border-b border-gray-200 px-6 py-3 text-sm dark:border-zinc-800">
            <span class="flex items-center gap-2 {{ $closeStep == 1 ? 'font-semibold text-primary' : 'text-emerald-600 dark:text-emerald-400' }}">
                <span style="width:1.5rem;height:1.5rem" class="flex items-center justify-center rounded-full border {{ $closeStep == 1 ? 'border-primary' : 'border-emerald-600' }}">
                    @if($closeStep == 1) 1 @else <i class="bi bi-check-lg"></i> @endif
                </span>
                Verificación QR
            </span>
            <span class="flex-1 border-t border-gray-300 dark:border-zinc-700"></span>
            <span class="flex items-center gap-2 {{ $closeStep == 2 ? 'font-semibold text-primary' : 'text-zinc-400 dark:text-zinc-500' }}">
                <span style="width:1.5rem;height:1.5rem" class="flex items-center justify-center rounded-full border {{ $closeStep == 2 ? 'border-primary' : 'border-gray-300 dark:border-zinc-600' }}">2</span>
                Efectivo (cortes)
            </span>
        </div>

        <div class="max-h-[70vh] space-y-4 overflow-y-auto px-6 py-5">

            {{-- ================= PASO 1: QR ================= --}}
            <div @class(['space-y-4', 'hidden' => $closeStep != 1])>
                <div class="rounded-lg border border-gray-200 bg-sky-100 p-3 text-sm text-sky-700 dark:border-zinc-700 dark:bg-sky-900/30 dark:text-sky-300">
                    <i class="bi bi-qr-code mr-1"></i>
                    Revise en la app del banco cuánto llegó por QR en este turno y compárelo con lo que registra el sistema.
                </div>

                <div>
                    <div class="mb-1 flex justify-between text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        <span>Ventas por QR registradas</span>
                        <span>{{ $qrSales->count() }} {{ $qrSales->count() === 1 ? 'venta' : 'ventas' }}</span>
                    </div>
                    <div class="max-h-48 overflow-y-auto rounded-lg border border-gray-200 dark:border-zinc-700">
                        <table class="min-w-full text-sm">
                            <thead class="sticky top-0 bg-gray-100 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-3 py-1.5 text-left text-xs text-zinc-600 dark:text-zinc-300">VENTA</th>
                                    <th class="px-3 py-1.5 text-left text-xs text-zinc-600 dark:text-zinc-300">HORA</th>
                                    <th class="px-3 py-1.5 text-right text-xs text-zinc-600 dark:text-zinc-300">MONTO</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                                @forelse($qrSales as $sale)
                                    <tr class="bg-white dark:bg-zinc-900">
                                        <td class="px-3 py-1.5 text-zinc-700 dark:text-zinc-300">
                                            #{{ $sale->id }}
                                            @if($sale->isMixed())
                                                <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700 dark:bg-amber-900/30 dark:text-amber-300" title="Total {{ number_format($sale->total, 2) }}: QR {{ number_format($sale->qrPart(), 2) }} + efectivo {{ number_format($sale->cashPart(), 2) }}">Mixto</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-1.5 text-zinc-700 dark:text-zinc-300">{{ $sale->created_at?->format('H:i') }}</td>
                                        <td class="px-3 py-1.5 text-right font-medium text-zinc-800 dark:text-zinc-100">{{ number_format($sale->qrPart(), 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-3 text-center text-xs text-zinc-500 dark:text-zinc-400">No hubo ventas por QR en esta caja.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 text-base dark:border-zinc-700 dark:bg-zinc-900">
                    <span class="font-semibold text-zinc-700 dark:text-zinc-200">QR según el sistema</span>
                    <span class="font-bold text-sky-700 dark:text-sky-300">{{ number_format($qrExpected, 2) }}</span>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                        Monto que llegó a la cuenta por QR <span class="text-red-600">*</span>
                    </label>
                    <div class="flex gap-2">
                        <input type="number" step="0.01" min="0" inputmode="decimal" placeholder="0.00"
                            wire:model.live.debounce.400ms="closeForm.qr_amount"
                            class="w-full rounded-lg border px-3 py-2 text-right dark:bg-zinc-800 dark:text-zinc-100 {{ $errors->has('closeForm.qr_amount') ? 'border-red-500' : 'border-gray-300 dark:border-zinc-700' }}">
                        <button type="button" wire:click="qrMatches"
                            class="whitespace-nowrap rounded-lg border border-emerald-600 px-3 py-2 text-sm text-emerald-700 hover:bg-gray-100 dark:text-emerald-300 dark:hover:bg-emerald-900/20 cursor-pointer">
                            <i class="bi bi-check2-circle"></i> Coincide
                        </button>
                    </div>
                    @error('closeForm.qr_amount')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                @if($qrTyped)
                    <div class="flex justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-base dark:border-zinc-700 dark:bg-zinc-800/50">
                        <span class="font-semibold text-zinc-700 dark:text-zinc-200">Diferencia QR — {{ $difText($qrDifference) }}</span>
                        <span class="font-bold {{ $difClass($qrDifference) }}">{{ $qrDifference > 0 ? '+' : '' }}{{ number_format($qrDifference, 2) }}</span>
                    </div>
                    @if($qrDifference != 0)
                        <div class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
                            <i class="bi bi-exclamation-triangle mr-1"></i>
                            El QR no cuadra. Revise las ventas de la lista antes de continuar; si sigue igual, la diferencia quedará registrada en el cierre.
                        </div>
                    @endif
                @endif
            </div>

            {{-- ================= PASO 2: EFECTIVO ================= --}}
            <div @class(['space-y-4', 'hidden' => $closeStep != 2])>
                @if($closeStep == 2)
                    <div class="flex items-center justify-between rounded-lg border px-4 py-2 text-sm {{ $qrDifference == 0 ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/20' : 'border-amber-300 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20' }}">
                        <span class="text-zinc-700 dark:text-zinc-200">
                            <i class="bi bi-qr-code mr-1"></i>QR verificado: <span class="font-semibold">{{ number_format((float) $closeForm->qr_amount, 2) }}</span>
                            de {{ number_format($qrExpected, 2) }}
                        </span>
                        <span class="font-semibold {{ $difClass($qrDifference) }}">
                            {{ $qrDifference == 0 ? 'Cuadra' : ($qrDifference > 0 ? '+' : '') . number_format($qrDifference, 2) }}
                        </span>
                    </div>
                @endif

                @if($b)
                    <div class="rounded-lg border border-gray-200 bg-white p-3 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Efectivo que debe haber en caja</div>
                        <div class="flex justify-between text-zinc-700 dark:text-zinc-300">
                            <span>Monto inicial (cortes de apertura)</span><span>{{ number_format($b['opening'], 2) }}</span>
                        </div>
                        <div class="flex justify-between text-zinc-700 dark:text-zinc-300">
                            <span>+ Ventas en efectivo</span><span>{{ number_format($b['cash_sales'], 2) }}</span>
                        </div>
                        @if($b['incomes'] > 0)
                            <div class="flex justify-between text-zinc-700 dark:text-zinc-300">
                                <span>+ Ingresos manuales</span><span>{{ number_format($b['incomes'], 2) }}</span>
                            </div>
                        @endif
                        @if($b['expenses'] > 0)
                            <div class="flex justify-between text-zinc-700 dark:text-zinc-300">
                                <span>− Egresos manuales</span><span>{{ number_format($b['expenses'], 2) }}</span>
                            </div>
                        @endif
                        <div class="mt-1 flex justify-between border-t border-gray-200 pt-1 text-base font-bold text-emerald-700 dark:border-zinc-700 dark:text-emerald-300">
                            <span>= Efectivo esperado</span><span>{{ number_format($b['expected_cash'], 2) }}</span>
                        </div>
                    </div>
                @endif

                <div class="border-t border-gray-200 pt-4 dark:border-zinc-700">
                    @include('livewire.caja.partials.cortes', ['target' => 'closeForm.closing_amount', 'label' => 'Efectivo contado'])
                </div>

                <div class="flex justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-base dark:border-zinc-700 dark:bg-zinc-800/50">
                    <span class="font-semibold text-zinc-700 dark:text-zinc-200">Diferencia efectivo — {{ $difText($projectedDifference) }}</span>
                    <span class="font-bold {{ $difClass($projectedDifference) }}">
                        {{ $projectedDifference > 0 ? '+' : '' }}{{ number_format($projectedDifference, 2) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
            @if($closeStep == 1)
                <button wire:click="closeCloseCashModal"
                    class="rounded-lg border border-red-300 bg-white px-4 py-2 text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20 cursor-pointer">
                    Cancelar
                </button>
                <button wire:click="verifyQr"
                    class="rounded-lg border border-primary bg-white px-4 py-2 text-primary hover:bg-primary/10 dark:bg-zinc-800 dark:hover:bg-primary/20 cursor-pointer">
                    Verificar QR y continuar <i class="bi bi-arrow-right"></i>
                </button>
            @else
                <button wire:click="backToQr"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-zinc-700 hover:bg-gray-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 cursor-pointer">
                    <i class="bi bi-arrow-left"></i> Volver al QR
                </button>
                <button wire:click="closeCashRegister"
                    class="rounded-lg border border-primary bg-white px-4 py-2 text-primary hover:bg-primary/10 dark:bg-zinc-800 dark:hover:bg-primary/20 cursor-pointer">
                    Cerrar caja
                </button>
            @endif
        </div>
    </div>
</div>
