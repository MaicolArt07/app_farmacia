@php
    $activeSales = $cash->sales->where('status', 'ACTIVE');
    $byMethod = $activeSales->groupBy('metodo_pago')->map(fn ($group) => $group->sum('total'));
    $totalSales = $activeSales->sum('total');
    // Solo movimientos MANUALES (los automáticos de venta/anulación no se listan).
    $manualMovements = $cash->movements->filter(fn ($m) =>
        !in_array($m->reference_type, ['SALE', 'SALE_CANCEL'], true)
        && !str_starts_with((string) $m->concepto, 'Anulación venta #'));
    // Desglose del EFECTIVO esperado (el QR no entra al cajón).
    $b = $cash->cashBreakdown();
    $totalIncomes = $b['incomes'];
    $totalExpenses = $b['expenses'];
@endphp

<div class="space-y-4">
    <div class="grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Usuario</div>
            <div class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $cash->user?->name ?? '-' }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Monto inicial</div>
            <div class="font-semibold text-zinc-800 dark:text-zinc-100">{{ number_format($cash->monto_apertura, 2) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Ventas registradas</div>
            <div class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $activeSales->count() }}</div>
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-800 dark:bg-emerald-900/20">
            <div class="text-xs text-emerald-700 dark:text-emerald-300">Efectivo esperado</div>
            <div class="font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($b['expected_cash'], 2) }}</div>
        </div>
    </div>

    {{-- Cómo se calcula el efectivo que debe haber en el cajón --}}
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
        <div class="mt-1 flex justify-between border-t border-gray-200 pt-1 font-bold text-emerald-700 dark:border-zinc-700 dark:text-emerald-300">
            <span>= Efectivo esperado</span><span>{{ number_format($b['expected_cash'], 2) }}</span>
        </div>
        @if($b['qr_sales'] > 0)
            <div class="mt-2 rounded bg-sky-100 px-2 py-1 text-xs text-sky-700 dark:bg-sky-900/30 dark:text-sky-300">
                <i class="bi bi-qr-code mr-1"></i>Ventas por QR: <span class="font-semibold">{{ number_format($b['qr_sales'], 2) }}</span>
                — van a la cuenta bancaria, <span class="font-semibold">no se cuentan en los cortes</span>.
            </div>
        @endif
    </div>

    <div>
        <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Ventas por método de pago</div>
        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
            <table class="min-w-full text-sm">
                <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                    @forelse($byMethod as $method => $sum)
                        <tr class="bg-white dark:bg-zinc-900">
                            <td class="px-3 py-1.5 text-zinc-700 dark:text-zinc-300">{{ $method }}</td>
                            <td class="px-3 py-1.5 text-right font-medium text-zinc-800 dark:text-zinc-100">{{ number_format($sum, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-3 py-2 text-center text-xs text-zinc-500 dark:text-zinc-400">Sin ventas registradas.</td>
                        </tr>
                    @endforelse
                    <tr class="bg-gray-50 font-semibold dark:bg-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">Total ventas</td>
                        <td class="px-3 py-1.5 text-right text-zinc-800 dark:text-zinc-100">{{ number_format($totalSales, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if($manualMovements->isNotEmpty())
        <div>
            <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Movimientos manuales</div>
            <div class="max-h-40 overflow-y-auto rounded-lg border border-gray-200 dark:border-zinc-700">
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                        @foreach($manualMovements as $movement)
                            <tr class="bg-white dark:bg-zinc-900">
                                <td class="px-3 py-1.5 text-zinc-700 dark:text-zinc-300">{{ $movement->concepto }}</td>
                                <td class="px-3 py-1.5 text-center">
                                    <span class="rounded-full px-2 py-0.5 text-xs {{ $movement->type === 'INCOME' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                        {{ $movement->type === 'INCOME' ? 'Ingreso' : 'Egreso' }}
                                    </span>
                                </td>
                                <td class="px-3 py-1.5 text-right font-medium text-zinc-800 dark:text-zinc-100">{{ number_format($movement->monto, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-1 flex justify-between text-xs text-zinc-500 dark:text-zinc-400">
                <span>Ingresos manuales: {{ number_format($totalIncomes, 2) }}</span>
                <span>Egresos manuales: {{ number_format($totalExpenses, 2) }}</span>
            </div>
        </div>
    @endif

    @if($activeSales->isNotEmpty())
        <div>
            <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Detalle de ventas</div>
            <div class="max-h-48 overflow-y-auto rounded-lg border border-gray-200 dark:border-zinc-700">
                <table class="min-w-full text-sm">
                    <thead class="sticky top-0 bg-gray-100 dark:bg-zinc-800">
                        <tr>
                            <th class="px-3 py-1.5 text-left text-xs text-zinc-600 dark:text-zinc-300">FECHA</th>
                            <th class="px-3 py-1.5 text-left text-xs text-zinc-600 dark:text-zinc-300">PAGO</th>
                            <th class="px-3 py-1.5 text-right text-xs text-zinc-600 dark:text-zinc-300">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                        @foreach($activeSales as $sale)
                            <tr class="bg-white dark:bg-zinc-900">
                                <td class="px-3 py-1.5 text-zinc-700 dark:text-zinc-300">{{ \Carbon\Carbon::parse($sale->fecha_venta)->format('d/m/Y') }}</td>
                                <td class="px-3 py-1.5 text-zinc-700 dark:text-zinc-300">
                                    {{ $sale->metodo_pago }}
                                    @if($sale->isMixed())
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">(Ef. {{ number_format($sale->cashPart(), 2) }} · QR {{ number_format($sale->qrPart(), 2) }})</span>
                                    @endif
                                </td>
                                <td class="px-3 py-1.5 text-right font-medium text-zinc-800 dark:text-zinc-100">{{ number_format($sale->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
