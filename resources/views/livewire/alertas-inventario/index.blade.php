<div class="p-6 space-y-6">

    <h1 class="text-xl font-bold text-zinc-800 dark:text-zinc-100">
        Alertas de Inventario
    </h1>

    <div class="rounded-xl border border-red-200 bg-white p-4 dark:border-red-900/40 dark:bg-zinc-900">
        <h2 class="mb-3 font-semibold text-red-700 dark:text-red-300">
            Productos vencidos
        </h2>

        @forelse($expiredLots as $lot)
            <div class="border-b border-zinc-200 py-2 dark:border-zinc-800">
                <strong>{{ $lot->product?->nombre }}</strong>
                | Lote: {{ $lot->codigo_lote }}
                | Vence: {{ $lot->fecha_vencimiento->format('d/m/Y') }}
                | Stock: {{ $lot->cantidad_disponible }}
            </div>
        @empty
            <p class="text-sm text-zinc-500">No hay productos vencidos.</p>
        @endforelse
    </div>

    <div class="rounded-xl border border-amber-200 bg-white p-4 dark:border-amber-900/40 dark:bg-zinc-900">
        <h2 class="mb-3 font-semibold text-amber-700 dark:text-amber-300">
            Productos por vencer en 30 días
        </h2>

        @forelse($nearExpirationLots as $lot)
            <div class="border-b border-zinc-200 py-2 dark:border-zinc-800">
                <strong>{{ $lot->product?->nombre }}</strong>
                | Lote: {{ $lot->codigo_lote }}
                | Vence: {{ $lot->fecha_vencimiento->format('d/m/Y') }}
                | Stock: {{ $lot->cantidad_disponible }}
            </div>
        @empty
            <p class="text-sm text-zinc-500">No hay productos próximos a vencer.</p>
        @endforelse
    </div>

    <div class="rounded-xl border border-sky-200 bg-white p-4 dark:border-sky-900/40 dark:bg-zinc-900">
        <h2 class="mb-3 font-semibold text-sky-700 dark:text-sky-300">
            Productos con stock bajo
        </h2>

        @forelse($lowStockProducts as $product)
            @php
                $stock = $product->lots->where('estado', 1)->sum('cantidad_disponible');
            @endphp

            <div class="border-b border-zinc-200 py-2 dark:border-zinc-800">
                <strong>{{ $product->nombre }}</strong>
                | Código: {{ $product->code }}
                | Stock: {{ $stock }}
                | Mínimo: {{ $product->stock_minimo }}
            </div>
        @empty
            <p class="text-sm text-zinc-500">No hay productos con stock bajo.</p>
        @endforelse
    </div>

</div>