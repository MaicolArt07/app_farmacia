<x-layouts.app :title="__('Inicio')">

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <!-- Tarjetas de estadísticas -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">

            <!-- Notas de Recepción -->
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="text-xl font-bold text-gray-700 dark:text-zinc-200">Notas de Recepción</div>
                <div class="mt-4 text-5xl font-extrabold text-blue-600 dark:text-blue-400">
                    {{ $totalReceptionNotes }}
                </div>
            </div>

            <!-- Equipos -->
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="text-xl font-bold text-gray-700 dark:text-zinc-200">Equipos</div>
                <div class="mt-4 text-5xl font-extrabold text-green-600 dark:text-green-400">
                    {{ $totalDevices }}
                </div>
            </div>

            <!-- Clientes -->
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="text-xl font-bold text-gray-700 dark:text-zinc-200">Clientes</div>
                <div class="mt-4 text-5xl font-extrabold text-purple-600 dark:text-purple-400">
                    {{ $totalClients }}
                </div>
            </div>

        </div>
    
        <!-- Espacio para futuro gráfico o resumen -->
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
            <div class="mt-10 text-center text-gray-400 dark:text-zinc-400">
                Aquí puedes agregar un gráfico, actividades recientes o KPIs.
            </div>
        </div>

    </div>

</x-layouts.app>