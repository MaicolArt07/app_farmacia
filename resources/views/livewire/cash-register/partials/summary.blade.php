@php
    $activeSales = $cash->sales->where('status', 'ACTIVE');
    $byMethod = $activeSales->groupBy('payment_method')->map(fn ($group) => $group->sum('total'));
    $totalSales = $activeSales->sum('total');
    $totalIncomes = $cash->movements->where('type', 'INCOME')->sum('amount');
    $totalExpenses = $cash->movements->where('type', 'EXPENSE')->sum('amount');
@endphp

<div class="space-y-4">
    <div class="grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Usuario</div>
            <div class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $cash->user?->name ?? '-' }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Monto inicial</div>
            <div class="font-semibold text-zinc-800 dark:text-zinc-100">{{ number_format($cash->opening_amount, 2) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Ventas registradas</div>
            <div class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $activeSales->count() }}</div>
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-800 dark:bg-emerald-900/20">
            <div class="text-xs text-emerald-700 dark:text-emerald-300">Esperado</div>
            <div class="font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($cash->expected_amount, 2) }}</div>
        </div>
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

    @if($cash->movements->isNotEmpty())
        <div>
            <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Movimientos manuales</div>
            <div class="max-h-40 overflow-y-auto rounded-lg border border-gray-200 dark:border-zinc-700">
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                        @foreach($cash->movements as $movement)
                            <tr class="bg-white dark:bg-zinc-900">
                                <td class="px-3 py-1.5 text-zinc-700 dark:text-zinc-300">{{ $movement->concept }}</td>
                                <td class="px-3 py-1.5 text-center">
                                    <span class="rounded-full px-2 py-0.5 text-xs {{ $movement->type === 'INCOME' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                        {{ $movement->type === 'INCOME' ? 'Ingreso' : 'Egreso' }}
                                    </span>
                                </td>
                                <td class="px-3 py-1.5 text-right font-medium text-zinc-800 dark:text-zinc-100">{{ number_format($movement->amount, 2) }}</td>
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
                                <td class="px-3 py-1.5 text-zinc-700 dark:text-zinc-300">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
                                <td class="px-3 py-1.5 text-zinc-700 dark:text-zinc-300">{{ $sale->payment_method }}</td>
                                <td class="px-3 py-1.5 text-right font-medium text-zinc-800 dark:text-zinc-100">{{ number_format($sale->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
