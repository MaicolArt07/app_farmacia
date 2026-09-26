<div class="min-h-screen w-full">

    {{-- ============================================================
         1. ENCABEZADO
    ============================================================ --}}
    <div class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <div class="flex items-center gap-3">
                    <div class="h-8 w-1 rounded-full bg-slate-700 dark:bg-slate-400"></div>
                    <h1 class="text-xl font-semibold tracking-tight text-slate-800 dark:text-white sm:text-2xl">
                        Inicio
                    </h1>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                    Visión consolidada de ventas, productos e inventario.
                </p>
            </div>
            <div class="flex items-center gap-3 self-start sm:self-auto">
                <span class="hidden items-center gap-1.5 text-xs text-slate-400 dark:text-zinc-500 sm:inline-flex">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    Actualizado
                </span>
                <a href="{{ route('products') }}" wire:navigate
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition-all active:scale-95 hover:bg-slate-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                    <i class="bi bi-capsule-pill"></i>
                    <span>Ver Productos</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ============================================================
         2. FILTRO GLOBAL
    ============================================================ --}}
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

        <div class="mb-3 flex flex-wrap items-center gap-2">
            <span class="mr-1 text-xs font-medium text-slate-500 dark:text-zinc-400">Rápido:</span>

            @foreach ([
                'today' => 'Hoy',
                '7d' => '7 días',
                '30d' => '30 días',
                'month' => 'Este mes',
                'year' => 'Este año',
            ] as $key => $label)
                <button
                    wire:click="setPreset('{{ $key }}')"
                    class="cursor-pointer rounded-lg border px-3 py-1 text-xs font-medium transition-all {{ $range === $key ? 'border-slate-700 bg-slate-800 text-white dark:border-zinc-500 dark:bg-zinc-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-slate-500 dark:text-zinc-400">Desde</label>
                <input type="date" wire:model.live="from"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-800 focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400/30 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-slate-500 dark:text-zinc-400">Hasta</label>
                <input type="date" wire:model.live="to"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-800 focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400/30 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
            </div>

            <div class="flex items-center gap-2 pb-0.5">
                <button wire:click="applyFilter"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-slate-800 px-4 py-1.5 text-sm font-medium text-white transition-all hover:bg-slate-700 dark:bg-zinc-700 dark:hover:bg-zinc-600">
                    <i class="bi bi-funnel-fill text-xs"></i>
                    Aplicar
                </button>

                @if ($hasFilter)
                    <button wire:click="clearFilter"
                        class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-1.5 text-sm font-medium text-slate-600 transition-all hover:bg-slate-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <i class="bi bi-x-lg text-xs"></i>
                        Limpiar
                    </button>
                @endif
            </div>

            <div wire:loading class="flex items-center gap-2 pb-0.5">
                <i class="bi bi-arrow-repeat animate-spin text-slate-400"></i>
                <span class="text-xs font-medium text-slate-400">Actualizando...</span>
            </div>
        </div>

        @if ($hasFilter)
            <div class="mt-3 flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-2 text-xs text-slate-500 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-400">
                <i class="bi bi-funnel text-slate-400"></i>
                <span>Filtrando desde</span>
                <span class="font-semibold text-slate-700 dark:text-zinc-200">{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }}</span>
                <span>hasta</span>
                <span class="font-semibold text-slate-700 dark:text-zinc-200">{{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</span>
            </div>
        @endif
    </div>

    {{-- ============================================================
         3. KPIs
    ============================================================ --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" wire:loading.class="opacity-60">

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-zinc-400">Ventas</p>
                    <p class="mt-1.5 text-2xl font-bold text-slate-800 dark:text-white">{{ $salesCount }}</p>
                    <p class="mt-0.5 text-xs text-slate-400 dark:text-zinc-500">en el periodo</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                    <i class="bi bi-bag-check-fill text-lg"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-zinc-400">Total vendido</p>
                    <p class="mt-1.5 text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($salesTotal, 2) }}</p>
                    <p class="mt-0.5 text-xs text-slate-400 dark:text-zinc-500">Bs. en el periodo</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                    <i class="bi bi-cash-coin text-lg"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-zinc-400">Productos activos</p>
                    <p class="mt-1.5 text-2xl font-bold text-slate-800 dark:text-white">{{ $totalProducts }}</p>
                    <p class="mt-0.5 text-xs text-slate-400 dark:text-zinc-500">{{ $totalClients }} clientes activos</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                    <i class="bi bi-capsule-pill text-lg"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-zinc-400">Alertas de stock</p>
                    <p class="mt-1.5 text-2xl font-bold {{ $lowStockCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-800 dark:text-white' }}">{{ $lowStockCount }}</p>
                    <p class="mt-0.5 text-xs text-slate-400 dark:text-zinc-500">productos bajo el mínimo</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                    <i class="bi bi-exclamation-triangle-fill text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         4. TOP PRODUCTOS (barras)
    ============================================================ --}}
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="flex items-center gap-2 text-base font-semibold text-slate-800 dark:text-white">
                    <span class="h-5 w-0.5 rounded-full bg-slate-400"></span>
                    Top productos más vendidos
                </h2>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-zinc-400">Cantidad vendida por producto en el periodo.</p>
            </div>
            <span class="rounded-lg bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-400 dark:bg-zinc-800 dark:text-zinc-500">
                {{ $topProducts->count() }} productos
            </span>
        </div>

        @if ($topProducts->count() > 0)
            <div class="relative h-72 w-full">
                <canvas id="chart-top-products" wire:ignore></canvas>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="mb-3 rounded-lg bg-slate-100 p-4 text-slate-400 dark:bg-zinc-800 dark:text-zinc-500">
                    <i class="bi bi-graph-up text-2xl"></i>
                </div>
                <p class="text-sm font-medium text-slate-600 dark:text-zinc-300">Sin ventas registradas</p>
                <p class="mt-0.5 text-xs text-slate-400 dark:text-zinc-500">No hay productos vendidos en este periodo.</p>
            </div>
        @endif
    </div>

    {{-- ============================================================
         5. DOUGHNUT + LINE CHART
    ============================================================ --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

        <div class="flex flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4">
                <h3 class="flex items-center gap-2 text-base font-semibold text-slate-800 dark:text-white">
                    <span class="h-5 w-0.5 rounded-full bg-slate-400"></span>
                    Ventas por método de pago
                </h3>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-zinc-400">Distribución de ventas en el periodo.</p>
            </div>

            @if ($salesCount > 0)
                <div class="my-auto grid grid-cols-1 items-center gap-4 sm:grid-cols-12">
                    <div class="flex items-center justify-center sm:col-span-5">
                        <div class="relative h-[190px] w-[190px]">
                            <canvas id="chart-payment-method" wire:ignore></canvas>
                            <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                                <span class="text-2xl font-bold leading-none text-slate-800 dark:text-white">{{ $salesCount }}</span>
                                <span class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-zinc-500">Ventas</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5 sm:col-span-7">
                        @php $colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6']; @endphp
                        @foreach ($paymentMethodLabels as $i => $label)
                            @php
                                $value = $paymentMethodData[$i];
                                $pct = $salesCount > 0 ? round(($value / $salesCount) * 100) : 0;
                            @endphp
                            <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50/60 px-3 py-1.5 text-xs dark:border-zinc-800/60 dark:bg-zinc-800/30">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $colors[$i % count($colors)] }}"></span>
                                    <span class="truncate font-medium text-slate-700 dark:text-zinc-300">{{ ucfirst(strtolower($label)) }}</span>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <span class="font-semibold text-slate-700 dark:text-zinc-200">{{ $value }}</span>
                                    <span class="w-7 text-right text-[10px] text-slate-400 dark:text-zinc-500">{{ $pct }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="my-auto flex flex-col items-center justify-center py-12 text-center">
                    <div class="mb-3 rounded-lg bg-slate-100 p-4 text-slate-400 dark:bg-zinc-800 dark:text-zinc-500">
                        <i class="bi bi-inbox text-2xl"></i>
                    </div>
                    <p class="text-sm font-medium text-slate-600 dark:text-zinc-300">Sin datos de ventas</p>
                    <p class="mt-0.5 text-xs text-slate-400 dark:text-zinc-500">No hay ventas en el rango seleccionado.</p>
                </div>
            @endif
        </div>

        <div class="flex flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4">
                <h3 class="flex items-center gap-2 text-base font-semibold text-slate-800 dark:text-white">
                    <span class="h-5 w-0.5 rounded-full bg-slate-400"></span>
                    Evolución de ventas
                </h3>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-zinc-400">Ventas (Bs.) día por día en el periodo.</p>
            </div>

            @if ($salesCount > 0)
                <div class="relative mt-auto h-[220px] w-full">
                    <canvas id="chart-sales-trend" wire:ignore></canvas>
                </div>
            @else
                <div class="my-auto flex flex-col items-center justify-center py-12 text-center">
                    <div class="mb-3 rounded-lg bg-slate-100 p-4 text-slate-400 dark:bg-zinc-800 dark:text-zinc-500">
                        <i class="bi bi-activity text-2xl"></i>
                    </div>
                    <p class="text-sm font-medium text-slate-600 dark:text-zinc-300">Sin evolución</p>
                    <p class="mt-0.5 text-xs text-slate-400 dark:text-zinc-500">No se registraron ventas en este periodo.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================================
         6. TABLA DE PRODUCTOS
    ============================================================ --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="flex items-center gap-2 text-base font-semibold text-slate-800 dark:text-white">
                    <span class="h-5 w-0.5 rounded-full bg-slate-400"></span>
                    Resumen de productos
                </h3>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-zinc-400">Productos con mayor movimiento en el periodo.</p>
            </div>
            <a href="{{ route('products') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 transition-colors hover:text-slate-800 dark:text-zinc-300 dark:hover:text-white">
                <span>Ver todos</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-zinc-800">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-zinc-800 dark:bg-zinc-800/80 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-2.5">Producto</th>
                        <th class="px-3 py-2.5 text-center">Vendidos</th>
                        <th class="px-3 py-2.5 text-center">Ingresos</th>
                        <th class="px-3 py-2.5 text-center">Stock actual</th>
                        <th class="w-36 px-4 py-2.5">Progreso</th>
                        <th class="px-4 py-2.5 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                    @forelse ($topProducts as $item)
                        <tr class="transition-colors hover:bg-slate-50/80 dark:hover:bg-zinc-800/50">
                            <td class="px-4 py-2.5">
                                <div class="font-semibold text-slate-800 dark:text-zinc-100">{{ $item['name'] }}</div>
                                <div class="text-xs text-slate-400 dark:text-zinc-500">{{ $item['code'] }}</div>
                            </td>
                            <td class="px-3 py-2.5 text-center font-semibold text-slate-700 dark:text-zinc-200">{{ $item['quantity'] }}</td>
                            <td class="px-3 py-2.5 text-center text-slate-700 dark:text-zinc-200">{{ number_format($item['revenue'], 2) }}</td>
                            <td class="px-3 py-2.5 text-center text-slate-700 dark:text-zinc-200">{{ $item['stock'] }}</td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-zinc-800">
                                        <div class="h-full rounded-full bg-blue-500 transition-all duration-500" style="width: {{ $item['percent'] }}%"></div>
                                    </div>
                                    <span class="w-8 text-right text-xs font-semibold text-slate-600 dark:text-zinc-300">{{ $item['percent'] }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @if ($item['low_stock'])
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-red-200 bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 dark:border-red-800 dark:bg-red-950/30 dark:text-red-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Stock bajo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Activo
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-400 dark:text-zinc-500">
                                No se encontraron ventas en este periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@assets
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
@endassets

@script
<script>
    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    let topProductsChart, paymentMethodChart, salesTrendChart;

    function buildCharts() {
        const topProductsCtx = document.getElementById('chart-top-products');
        const paymentMethodCtx = document.getElementById('chart-payment-method');
        const salesTrendCtx = document.getElementById('chart-sales-trend');

        topProductsChart?.destroy();
        paymentMethodChart?.destroy();
        salesTrendChart?.destroy();

        const dark = isDark();
        const gridColor = dark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
        const tickColor = dark ? '#a1a1aa' : '#64748b';

        if (topProductsCtx) {
            const products = @json($topProducts->pluck('name'));
            const quantities = @json($topProducts->pluck('quantity'));

            topProductsChart = new Chart(topProductsCtx, {
                type: 'bar',
                data: {
                    labels: products,
                    datasets: [{
                        label: 'Cantidad vendida',
                        data: quantities,
                        backgroundColor: dark ? 'rgba(96,165,250,0.85)' : 'rgba(59,130,246,0.85)',
                        borderRadius: 6,
                        maxBarThickness: 42,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: tickColor } },
                        y: { beginAtZero: true, ticks: { precision: 0, color: tickColor }, grid: { color: gridColor } },
                    },
                },
            });
        }

        if (paymentMethodCtx) {
            const paymentData = @json($paymentMethodData);

            paymentMethodChart = new Chart(paymentMethodCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($paymentMethodLabels),
                    datasets: [{
                        data: paymentData,
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6'],
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: { legend: { display: false } },
                },
            });
        }

        if (salesTrendCtx) {
            salesTrendChart = new Chart(salesTrendCtx, {
                type: 'line',
                data: {
                    labels: @json($salesByDayLabels),
                    datasets: [{
                        label: 'Ventas (Bs.)',
                        data: @json($salesByDayData),
                        borderColor: dark ? '#60a5fa' : '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: tickColor } },
                        y: { beginAtZero: true, ticks: { color: tickColor }, grid: { color: gridColor } },
                    },
                },
            });
        }
    }

    buildCharts();
</script>
@endscript
