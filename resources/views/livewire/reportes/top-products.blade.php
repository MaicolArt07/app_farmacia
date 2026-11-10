<div class="space-y-4 bg-zinc-200 p-4 dark:bg-zinc-950">

    <div class="flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-xl font-bold text-zinc-800 dark:text-zinc-100">
            <i class="bi bi-trophy-fill mr-1 text-primary"></i> Productos más vendidos
        </h1>
        <a href="{{ route('reports') }}" wire:navigate
            class="rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200">
            <i class="bi bi-arrow-left"></i> Volver a reportes
        </a>
    </div>

    {{-- Filtro --}}
    <form wire:submit="generate"
        class="rounded border border-zinc-300 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Fecha inicio <span class="text-red-600">*</span></label>
                <input type="date" wire:model.live="from" max="{{ now()->toDateString() }}"
                    class="w-full rounded border bg-white px-2 py-1.5 text-sm text-zinc-800 dark:bg-zinc-800 dark:text-zinc-100 {{ $errors->has('from') ? 'border-red-500' : 'border-zinc-400 dark:border-zinc-600' }}">
                @error('from') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Fecha final <span class="text-red-600">*</span></label>
                <input type="date" wire:model.live="to" max="{{ now()->toDateString() }}"
                    class="w-full rounded border bg-white px-2 py-1.5 text-sm text-zinc-800 dark:bg-zinc-800 dark:text-zinc-100 {{ $errors->has('to') ? 'border-red-500' : 'border-zinc-400 dark:border-zinc-600' }}">
                @error('to') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Ordenar por</label>
                <select wire:model.live="order"
                    class="w-full rounded border border-zinc-400 bg-white px-2 py-1.5 text-sm text-zinc-800 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @foreach($orders as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded border border-primary bg-white px-3 py-1.5 text-sm font-medium text-primary hover:bg-primary/10 dark:bg-zinc-800">
                    <span wire:loading.remove wire:target="generate"><i class="bi bi-search"></i> Ver informe</span>
                    <span wire:loading wire:target="generate"><i class="bi bi-hourglass-split"></i> Consultando...</span>
                </button>
            </div>
        </div>
    </form>

    @if(!$totals)
        <div class="rounded border border-dashed border-zinc-400 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">
            <i class="bi bi-funnel text-2xl"></i>
            <p class="mt-2">Elija las fechas y presione <strong>Ver informe</strong>. Después podrá exportarlo a PDF o Excel.</p>
        </div>
    @else
        {{-- Encabezado del resultado + exportar --}}
        <div class="flex flex-wrap items-center justify-between gap-2 rounded border border-zinc-300 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-sm text-zinc-700 dark:text-zinc-200">
                Del <strong>{{ \Carbon\Carbon::parse($shownFrom)->format('d/m/Y') }}</strong>
                al <strong>{{ \Carbon\Carbon::parse($shownTo)->format('d/m/Y') }}</strong>
                · ordenado por {{ mb_strtolower($orders[$shownOrder]) }}
            </div>

            @if($dirty)
                <span class="rounded bg-amber-50 px-3 py-1.5 text-xs text-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
                    <i class="bi bi-exclamation-triangle"></i> Cambió el filtro: presione <strong>Ver informe</strong> para exportar.
                </span>
            @elseif($rows->isNotEmpty())
                <div class="flex gap-2">
                    <a href="{{ route('reports.top-products.pdf', ['from' => $shownFrom, 'to' => $shownTo, 'order' => $shownOrder]) }}" target="_blank"
                        class="flex items-center gap-1 rounded border border-red-300 bg-white px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-800 dark:text-red-400">
                        <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
                    </a>
                    <a href="{{ route('reports.top-products.excel', ['from' => $shownFrom, 'to' => $shownTo, 'order' => $shownOrder]) }}"
                        class="flex items-center gap-1 rounded border border-emerald-300 bg-white px-3 py-1.5 text-sm font-medium text-emerald-700 hover:bg-emerald-50 dark:border-emerald-800 dark:bg-zinc-800 dark:text-emerald-300">
                        <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                    </a>
                </div>
            @endif
        </div>

        {{-- Resumen --}}
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            <div class="rounded border border-zinc-300 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">Productos vendidos</div>
                <div class="text-lg font-bold text-zinc-800 dark:text-zinc-100">{{ $totals['productos'] }}</div>
            </div>
            <div class="rounded border border-zinc-300 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">Unidades vendidas</div>
                <div class="text-lg font-bold text-zinc-800 dark:text-zinc-100">{{ $totals['cantidad'] }}</div>
            </div>
            <div class="rounded border border-zinc-300 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">Total vendido</div>
                <div class="text-lg font-bold text-zinc-800 dark:text-zinc-100">Bs {{ number_format($totals['total_vendido'], 2) }}</div>
            </div>
            <div class="rounded border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-800 dark:bg-emerald-900/20">
                <div class="text-xs text-emerald-700 dark:text-emerald-300">Ganancia estimada</div>
                <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300">Bs {{ number_format($totals['ganancia'], 2) }}</div>
            </div>
        </div>

        @if($rows->isEmpty())
            <div class="rounded border border-zinc-300 bg-white p-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                No hubo ventas de productos en este período.
            </div>
        @else
            {{-- Tabla (escritorio) --}}
            <div class="hidden overflow-auto rounded border border-zinc-300 md:block dark:border-zinc-700">
                <table class="min-w-full border-collapse text-sm">
                    <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                        <tr>
                            <th class="px-3 py-1.5 text-center font-semibold text-zinc-700 dark:text-zinc-200">N°</th>
                            <th class="px-3 py-1.5 text-left font-semibold text-zinc-700 dark:text-zinc-200">PRODUCTO</th>
                            <th class="px-3 py-1.5 text-left font-semibold text-zinc-700 dark:text-zinc-200">LABORATORIO</th>
                            <th class="px-3 py-1.5 text-right font-semibold text-zinc-700 dark:text-zinc-200">CANTIDAD</th>
                            <th class="px-3 py-1.5 text-right font-semibold text-zinc-700 dark:text-zinc-200">VENTAS</th>
                            <th class="px-3 py-1.5 text-right font-semibold text-zinc-700 dark:text-zinc-200">P. PROM.</th>
                            <th class="px-3 py-1.5 text-right font-semibold text-zinc-700 dark:text-zinc-200">TOTAL (Bs)</th>
                            <th class="px-3 py-1.5 text-right font-semibold text-zinc-700 dark:text-zinc-200">COSTO (Bs)</th>
                            <th class="px-3 py-1.5 text-right font-semibold text-zinc-700 dark:text-zinc-200">GANANCIA (Bs)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900">
                        @foreach($rows as $r)
                            <tr class="border-b border-zinc-200 dark:border-zinc-800">
                                <td class="px-3 py-1.5 text-center">
                                    @if($r->puesto <= 3)
                                        <span class="font-bold {{ ['', 'text-amber-500', 'text-zinc-400', 'text-amber-700'][$r->puesto] }}"><i class="bi bi-trophy-fill"></i> {{ $r->puesto }}</span>
                                    @else
                                        <span class="text-zinc-600 dark:text-zinc-300">{{ $r->puesto }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                                    {{ $r->nombre }}
                                    <div class="font-mono text-xs text-zinc-400">{{ $r->code }}</div>
                                </td>
                                <td class="px-3 py-1.5 text-zinc-600 dark:text-zinc-300">{{ $r->laboratorio ?? '-' }}</td>
                                <td class="px-3 py-1.5 text-right font-bold text-zinc-800 dark:text-zinc-100">{{ $r->cantidad }}</td>
                                <td class="px-3 py-1.5 text-right text-zinc-600 dark:text-zinc-300">{{ $r->num_ventas }}</td>
                                <td class="px-3 py-1.5 text-right text-zinc-600 dark:text-zinc-300">{{ number_format($r->precio_promedio, 2) }}</td>
                                <td class="px-3 py-1.5 text-right font-semibold text-zinc-800 dark:text-zinc-100">{{ number_format($r->total_vendido, 2) }}</td>
                                <td class="px-3 py-1.5 text-right text-zinc-600 dark:text-zinc-300">{{ number_format($r->costo, 2) }}</td>
                                <td class="px-3 py-1.5 text-right font-semibold {{ $r->ganancia < 0 ? 'text-red-600' : 'text-emerald-700 dark:text-emerald-300' }}">{{ number_format($r->ganancia, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-zinc-100 font-bold dark:bg-zinc-800">
                        <tr>
                            <td colspan="3" class="px-3 py-2 text-zinc-800 dark:text-zinc-100">TOTAL</td>
                            <td class="px-3 py-2 text-right text-zinc-800 dark:text-zinc-100">{{ $totals['cantidad'] }}</td>
                            <td colspan="2"></td>
                            <td class="px-3 py-2 text-right text-zinc-800 dark:text-zinc-100">{{ number_format($totals['total_vendido'], 2) }}</td>
                            <td class="px-3 py-2 text-right text-zinc-800 dark:text-zinc-100">{{ number_format($totals['costo'], 2) }}</td>
                            <td class="px-3 py-2 text-right text-emerald-700 dark:text-emerald-300">{{ number_format($totals['ganancia'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Tarjetas (móvil) --}}
            <div class="flex flex-col gap-2 md:hidden">
                @foreach($rows as $r)
                    <div class="rounded border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $r->puesto }}. {{ $r->nombre }}</span>
                            <span class="shrink-0 text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ $r->cantidad }} u.</span>
                        </div>
                        <div class="mt-1 flex justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <span>Total Bs {{ number_format($r->total_vendido, 2) }}</span>
                            <span>Ganancia Bs {{ number_format($r->ganancia, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <p class="text-xs text-zinc-500 dark:text-zinc-400">
            Solo cuenta ventas activas (las canceladas no). No incluye servicios. La ganancia usa el precio de compra del lote vendido.
        </p>
    @endif
</div>
