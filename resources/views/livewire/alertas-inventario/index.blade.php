<div class="flex flex-col gap-4 bg-zinc-200 p-4 dark:bg-zinc-950">
    @php
        $th = 'border-b border-zinc-400 px-3 py-1.5 font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200';
        $td = 'px-3 py-1.5 text-zinc-800 dark:text-zinc-100';
    @endphp

    {{-- Encabezado + buscador --}}
    <div class="flex flex-col gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900 md:flex-row md:items-center md:justify-between">
        <h1 class="text-lg font-bold text-zinc-800 dark:text-zinc-100">
            <i class="bi bi-bell-fill mr-1 text-primary"></i> Alertas de inventario
        </h1>
        <input type="text" wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nombre o código..."
            class="w-full rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-primary focus:outline-none md:w-80 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500">
    </div>

    {{-- Resumen: clic para ir a cada sección --}}
    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
        <a href="#vencidos" class="rounded border border-red-300 bg-white px-4 py-3 hover:bg-zinc-100 dark:border-red-800 dark:hover:bg-zinc-800 dark:bg-zinc-900">
            <div class="text-xs font-medium uppercase tracking-wide text-red-700 dark:text-red-300">
                <i class="bi bi-x-octagon-fill mr-1"></i>Vencidos
            </div>
            <div class="mt-1 text-2xl font-bold text-zinc-800 dark:text-zinc-100">{{ $expiredLots->count() }}</div>
        </a>
        <a href="#por-vencer" class="rounded border border-amber-300 bg-white px-4 py-3 hover:bg-zinc-100 dark:border-amber-800 dark:hover:bg-zinc-800 dark:bg-zinc-900">
            <div class="text-xs font-medium uppercase tracking-wide text-amber-700 dark:text-amber-300">
                <i class="bi bi-hourglass-split mr-1"></i>Por vencer (30 días)
            </div>
            <div class="mt-1 text-2xl font-bold text-zinc-800 dark:text-zinc-100">{{ $nearExpirationLots->count() }}</div>
        </a>
        <a href="#stock-bajo" class="rounded border border-primary bg-white px-4 py-3 hover:bg-zinc-100 dark:hover:bg-zinc-800 dark:bg-zinc-900">
            <div class="text-xs font-medium uppercase tracking-wide text-sky-700 dark:text-sky-300">
                <i class="bi bi-box-seam mr-1"></i>Stock bajo
            </div>
            <div class="mt-1 text-2xl font-bold text-zinc-800 dark:text-zinc-100">{{ $lowStockProducts->count() }}</div>
        </a>
    </div>

    {{-- ═══════════ VENCIDOS ═══════════ --}}
    <div id="vencidos" class="rounded border border-zinc-300 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <h2 class="flex items-center justify-between border-b border-zinc-300 px-4 py-2 font-semibold text-red-700 dark:border-zinc-700 dark:text-red-300">
            <span><i class="bi bi-x-octagon-fill mr-1"></i> Productos vencidos</span>
            <span class="rounded-full bg-red-100 px-3 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ $expiredLots->count() }}</span>
        </h2>

        @if($expiredLots->isEmpty())
            <p class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">No hay productos vencidos.</p>
        @else
            <div class="hidden overflow-auto md:block">
                <table class="min-w-full border-collapse text-sm">
                    <thead class="bg-zinc-300 dark:bg-zinc-800">
                        <tr>
                            <th class="{{ $th }} text-left">Producto</th>
                            <th class="{{ $th }} text-left">Código</th>
                            <th class="{{ $th }} text-left">Lote</th>
                            <th class="{{ $th }} text-center">Venció</th>
                            <th class="{{ $th }} text-center">Hace</th>
                            <th class="{{ $th }} text-right">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiredLots as $lot)
                            <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                                <td class="{{ $td }}">
                                    <div class="font-medium">{{ $lot->product?->nombre }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $lot->product?->laboratory?->nombre ?? 'Sin laboratorio' }}</div>
                                </td>
                                <td class="{{ $td }}">{{ $lot->product?->code }}</td>
                                <td class="{{ $td }}">{{ $lot->codigo_lote }}</td>
                                <td class="{{ $td }} text-center">{{ $lot->fecha_vencimiento->format('d/m/Y') }}</td>
                                <td class="{{ $td }} text-center">
                                    <span class="rounded-full bg-red-100 px-3 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                        {{ $lot->days }} {{ $lot->days === 1 ? 'día' : 'días' }}
                                    </span>
                                </td>
                                <td class="{{ $td }} text-right font-semibold">{{ $lot->cantidad_disponible }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col md:hidden">
                @foreach($expiredLots as $lot)
                    <div class="border-b border-zinc-200 px-4 py-2 dark:border-zinc-800">
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $lot->product?->nombre }}</span>
                            <span class="shrink-0 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">hace {{ $lot->days }} d</span>
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            Venció {{ $lot->fecha_vencimiento->format('d/m/Y') }} · Lote {{ $lot->codigo_lote }} · Stock {{ $lot->cantidad_disponible }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ═══════════ POR VENCER ═══════════ --}}
    <div id="por-vencer" class="rounded border border-zinc-300 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <h2 class="flex items-center justify-between border-b border-zinc-300 px-4 py-2 font-semibold text-amber-700 dark:border-zinc-700 dark:text-amber-300">
            <span><i class="bi bi-hourglass-split mr-1"></i> Productos por vencer en 30 días</span>
            <span class="rounded-full bg-amber-100 px-3 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">{{ $nearExpirationLots->count() }}</span>
        </h2>

        @if($nearExpirationLots->isEmpty())
            <p class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">No hay productos próximos a vencer.</p>
        @else
            <div class="hidden overflow-auto md:block">
                <table class="min-w-full border-collapse text-sm">
                    <thead class="bg-zinc-300 dark:bg-zinc-800">
                        <tr>
                            <th class="{{ $th }} text-left">Producto</th>
                            <th class="{{ $th }} text-left">Código</th>
                            <th class="{{ $th }} text-left">Lote</th>
                            <th class="{{ $th }} text-center">Vence</th>
                            <th class="{{ $th }} text-center">Faltan</th>
                            <th class="{{ $th }} text-right">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nearExpirationLots as $lot)
                            <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                                <td class="{{ $td }}">
                                    <div class="font-medium">{{ $lot->product?->nombre }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $lot->product?->laboratory?->nombre ?? 'Sin laboratorio' }}</div>
                                </td>
                                <td class="{{ $td }}">{{ $lot->product?->code }}</td>
                                <td class="{{ $td }}">{{ $lot->codigo_lote }}</td>
                                <td class="{{ $td }} text-center">{{ $lot->fecha_vencimiento->format('d/m/Y') }}</td>
                                <td class="{{ $td }} text-center">
                                    <span class="rounded-full px-3 py-0.5 text-xs font-medium {{ $lot->days <= 7 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">
                                        {{ $lot->days === 0 ? 'Vence hoy' : $lot->days . ' ' . ($lot->days === 1 ? 'día' : 'días') }}
                                    </span>
                                </td>
                                <td class="{{ $td }} text-right font-semibold">{{ $lot->cantidad_disponible }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col md:hidden">
                @foreach($nearExpirationLots as $lot)
                    <div class="border-b border-zinc-200 px-4 py-2 dark:border-zinc-800">
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $lot->product?->nombre }}</span>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $lot->days <= 7 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">
                                {{ $lot->days === 0 ? 'hoy' : $lot->days . ' d' }}
                            </span>
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            Vence {{ $lot->fecha_vencimiento->format('d/m/Y') }} · Lote {{ $lot->codigo_lote }} · Stock {{ $lot->cantidad_disponible }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ═══════════ STOCK BAJO ═══════════ --}}
    <div id="stock-bajo" class="rounded border border-zinc-300 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <h2 class="flex items-center justify-between border-b border-zinc-300 px-4 py-2 font-semibold text-sky-700 dark:border-zinc-700 dark:text-sky-300">
            <span><i class="bi bi-box-seam mr-1"></i> Productos con stock bajo</span>
            <span class="rounded-full bg-sky-100 px-3 py-0.5 text-xs font-medium text-sky-700 dark:bg-sky-900/30 dark:text-sky-300">{{ $lowStockProducts->count() }}</span>
        </h2>

        @if($lowStockProducts->isEmpty())
            <p class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">No hay productos con stock bajo.</p>
        @else
            <div class="hidden overflow-auto md:block">
                <table class="min-w-full border-collapse text-sm">
                    <thead class="bg-zinc-300 dark:bg-zinc-800">
                        <tr>
                            <th class="{{ $th }} text-left">Producto</th>
                            <th class="{{ $th }} text-left">Código</th>
                            <th class="{{ $th }} text-right">Stock actual</th>
                            <th class="{{ $th }} text-right">Mínimo</th>
                            <th class="{{ $th }} text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockProducts as $product)
                            <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                                <td class="{{ $td }}">
                                    <div class="font-medium">{{ $product->nombre }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product->laboratory?->nombre ?? 'Sin laboratorio' }}</div>
                                </td>
                                <td class="{{ $td }}">{{ $product->code }}</td>
                                <td class="{{ $td }} text-right font-semibold">{{ $product->stock_actual }}</td>
                                <td class="{{ $td }} text-right">{{ $product->stock_minimo }}</td>
                                <td class="{{ $td }} text-center">
                                    @if($product->stock_actual <= 0)
                                        <span class="rounded-full bg-red-100 px-3 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">Agotado</span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-3 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">Bajo</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col md:hidden">
                @foreach($lowStockProducts as $product)
                    <div class="border-b border-zinc-200 px-4 py-2 dark:border-zinc-800">
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $product->nombre }}</span>
                            @if($product->stock_actual <= 0)
                                <span class="shrink-0 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">Agotado</span>
                            @else
                                <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">Bajo</span>
                            @endif
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            Código {{ $product->code }} · Stock {{ $product->stock_actual }} · Mínimo {{ $product->stock_minimo }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
