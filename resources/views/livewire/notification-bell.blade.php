<div>
    @can('Ver Alertas Inventario')
    <flux:dropdown position="bottom" align="end">
        <button type="button" class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg text-zinc-600 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
            <i class="bi bi-bell-fill text-lg"></i>
            @if($total > 0)
                <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-semibold text-white">
                    {{ $total > 99 ? '99+' : $total }}
                </span>
            @endif
        </button>

        <flux:menu class="w-80 dark:bg-zinc-900 dark:text-zinc-100">
            <div class="px-3 py-2 text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                Notificaciones
            </div>
            <flux:menu.separator />

            @if($total === 0)
                <div class="px-3 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                    No hay alertas pendientes.
                </div>
            @else
                @if($lowStockCount > 0)
                    <div class="px-3 py-2">
                        <div class="mb-1 flex items-center gap-2 text-sm font-medium text-amber-700 dark:text-amber-400">
                            <i class="bi bi-box-seam"></i> Stock bajo ({{ $lowStockCount }})
                        </div>
                        <ul class="space-y-0.5 text-xs text-zinc-600 dark:text-zinc-300">
                            @foreach($lowStockProducts as $product)
                                <li class="truncate">{{ $product->code }} - {{ $product->name }}</li>
                            @endforeach
                            @if($lowStockCount > 5)
                                <li class="text-zinc-400">y {{ $lowStockCount - 5 }} más...</li>
                            @endif
                        </ul>
                    </div>
                    <flux:menu.separator />
                @endif

                @if($expiredCount > 0)
                    <div class="px-3 py-2 text-sm text-red-700 dark:text-red-400">
                        <i class="bi bi-exclamation-octagon-fill me-1"></i>
                        {{ $expiredCount }} {{ $expiredCount === 1 ? 'lote vencido' : 'lotes vencidos' }} con stock disponible.
                    </div>
                    <flux:menu.separator />
                @endif

                @if($nearExpirationCount > 0)
                    <div class="px-3 py-2 text-sm text-orange-700 dark:text-orange-400">
                        <i class="bi bi-hourglass-split me-1"></i>
                        {{ $nearExpirationCount }} {{ $nearExpirationCount === 1 ? 'lote próximo' : 'lotes próximos' }} a vencer (30 días).
                    </div>
                    <flux:menu.separator />
                @endif
            @endif

            <flux:menu.item :href="route('inventory-alerts')" icon="arrow-right" wire:navigate>
                Ver todas las alertas
            </flux:menu.item>
        </flux:menu>
    </flux:dropdown>
    @endcan
</div>
