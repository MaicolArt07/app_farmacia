<div class="space-y-4 bg-zinc-200 p-4 dark:bg-zinc-950">

    <h1 class="text-xl font-bold text-zinc-800 dark:text-zinc-100">
        Reportes
    </h1>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">

        <!-- Libro Diario -->
        <div class="rounded border border-zinc-300 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-1 font-semibold text-zinc-800 dark:text-zinc-100">
                <i class="bi bi-journal-text me-1 text-primary"></i> Libro Diario
            </h2>
            <p class="mb-3 text-xs text-zinc-500 dark:text-zinc-400">
                Ventas, compras y movimientos de caja del período, con resumen de totales y diferencias de caja.
            </p>

            <form method="GET" action="{{ route('reports.daily-book.export') }}" class="space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="mb-1 block text-xs text-zinc-600 dark:text-zinc-300">Desde</label>
                        <input type="date" name="from" value="{{ $defaultFrom }}" required
                            class="w-full rounded border border-zinc-400 bg-white px-2 py-1.5 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-zinc-600 dark:text-zinc-300">Hasta</label>
                        <input type="date" name="to" value="{{ $defaultTo }}" required
                            class="w-full rounded border border-zinc-400 bg-white px-2 py-1.5 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                </div>
                <button type="submit"
                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                    <i class="bi bi-file-earmark-excel me-1 text-primary"></i> Descargar Excel
                </button>
            </form>
        </div>

        <!-- Ventas -->
        <div class="rounded border border-zinc-300 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-1 font-semibold text-zinc-800 dark:text-zinc-100">
                <i class="bi bi-bag-check-fill me-1 text-primary"></i> Ventas por fecha
            </h2>
            <p class="mb-3 text-xs text-zinc-500 dark:text-zinc-400">
                Detalle de todas las ventas registradas en el período seleccionado.
            </p>

            <form method="GET" action="{{ route('reports.sales.export') }}" class="space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="mb-1 block text-xs text-zinc-600 dark:text-zinc-300">Desde</label>
                        <input type="date" name="from" value="{{ $defaultFrom }}" required
                            class="w-full rounded border border-zinc-400 bg-white px-2 py-1.5 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-zinc-600 dark:text-zinc-300">Hasta</label>
                        <input type="date" name="to" value="{{ $defaultTo }}" required
                            class="w-full rounded border border-zinc-400 bg-white px-2 py-1.5 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                </div>
                <button type="submit"
                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                    <i class="bi bi-file-earmark-excel me-1"></i> Descargar Excel
                </button>
            </form>
        </div>

        <!-- Compras -->
        <div class="rounded border border-zinc-300 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-1 font-semibold text-zinc-800 dark:text-zinc-100">
                <i class="bi bi-cart-plus-fill me-1 text-primary"></i> Compras por fecha
            </h2>
            <p class="mb-3 text-xs text-zinc-500 dark:text-zinc-400">
                Detalle de todas las compras registradas en el período seleccionado.
            </p>

            <form method="GET" action="{{ route('reports.purchases.export') }}" class="space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="mb-1 block text-xs text-zinc-600 dark:text-zinc-300">Desde</label>
                        <input type="date" name="from" value="{{ $defaultFrom }}" required
                            class="w-full rounded border border-zinc-400 bg-white px-2 py-1.5 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-zinc-600 dark:text-zinc-300">Hasta</label>
                        <input type="date" name="to" value="{{ $defaultTo }}" required
                            class="w-full rounded border border-zinc-400 bg-white px-2 py-1.5 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                </div>
                <button type="submit"
                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                    <i class="bi bi-file-earmark-excel me-1"></i> Descargar Excel
                </button>
            </form>
        </div>

        <!-- Kardex -->
        <div class="rounded border border-zinc-300 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-1 font-semibold text-zinc-800 dark:text-zinc-100">
                <i class="bi bi-journal-medical me-1 text-primary"></i> Kárdex
            </h2>
            <p class="mb-3 text-xs text-zinc-500 dark:text-zinc-400">
                Movimientos de inventario (entradas, salidas, ajustes y anulaciones) del período seleccionado.
            </p>

            <form method="GET" action="{{ route('reports.kardex.export') }}" class="space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="mb-1 block text-xs text-zinc-600 dark:text-zinc-300">Desde</label>
                        <input type="date" name="from" value="{{ $defaultFrom }}" required
                            class="w-full rounded border border-zinc-400 bg-white px-2 py-1.5 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-zinc-600 dark:text-zinc-300">Hasta</label>
                        <input type="date" name="to" value="{{ $defaultTo }}" required
                            class="w-full rounded border border-zinc-400 bg-white px-2 py-1.5 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                </div>
                <button type="submit"
                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                    <i class="bi bi-file-earmark-excel me-1"></i> Descargar Excel
                </button>
            </form>
        </div>

        <!-- Stock actual -->
        <div class="rounded border border-zinc-300 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-1 font-semibold text-zinc-800 dark:text-zinc-100">
                <i class="bi bi-box-seam-fill me-1 text-primary"></i> Stock actual
            </h2>
            <p class="mb-3 text-xs text-zinc-500 dark:text-zinc-400">
                Todos los productos activos con su stock disponible, costo promedio y utilidad estimada. Foto del momento actual.
            </p>

            <a href="{{ route('reports.stock.export') }}"
                class="block w-full cursor-pointer rounded border border-zinc-400 bg-white px-3 py-1.5 text-center text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                <i class="bi bi-file-earmark-excel me-1"></i> Descargar Excel
            </a>
        </div>

        <!-- Alertas de inventario -->
        <div class="rounded border border-zinc-300 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-1 font-semibold text-zinc-800 dark:text-zinc-100">
                <i class="bi bi-exclamation-triangle-fill me-1 text-primary"></i> Alertas de Inventario
            </h2>
            <p class="mb-3 text-xs text-zinc-500 dark:text-zinc-400">
                Productos con stock faltante (igual o por debajo del mínimo), lotes vencidos y lotes por vencer en 30 días. Foto del momento actual.
            </p>

            <a href="{{ route('reports.inventory-alerts.export') }}"
                class="block w-full cursor-pointer rounded border border-zinc-400 bg-white px-3 py-1.5 text-center text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                <i class="bi bi-file-earmark-excel me-1"></i> Descargar Excel
            </a>
        </div>

    </div>
</div>
