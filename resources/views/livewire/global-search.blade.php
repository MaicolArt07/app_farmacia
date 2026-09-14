<div class="relative w-full max-w-md" x-data @click.away="$wire.close()">
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400">
            <i class="bi bi-search"></i>
        </span>
        <input
            type="text"
            wire:model.live.debounce.300ms="query"
            placeholder="Buscar clientes, productos..."
            class="w-full rounded-xl border border-zinc-300 bg-white py-2 pl-9 pr-3 text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-orange-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
        />
    </div>

    @if ($open && (count($clients) || count($products)))
        <div class="absolute z-50 mt-1 w-full overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
            @if (count($clients))
                <div class="border-b border-zinc-100 px-3 py-1.5 text-xs font-semibold uppercase text-zinc-400 dark:border-zinc-800">Clientes</div>
                @foreach ($clients as $client)
                    <a
                        href="{{ route('clients') }}"
                        wire:navigate
                        class="flex items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        <i class="bi bi-person-fill text-zinc-400"></i> {{ $client['label'] }}
                    </a>
                @endforeach
            @endif

            @if (count($products))
                <div class="border-b border-zinc-100 px-3 py-1.5 text-xs font-semibold uppercase text-zinc-400 dark:border-zinc-800">Productos</div>
                @foreach ($products as $product)
                    <a
                        href="{{ route('products') }}"
                        wire:navigate
                        class="flex items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        <i class="bi bi-capsule-pill text-zinc-400"></i> {{ $product['name'] }}
                        <span class="ml-auto text-xs text-zinc-400">{{ $product['code'] }}</span>
                    </a>
                @endforeach
            @endif
        </div>
    @elseif ($open && strlen(trim($query)) >= 2)
        <div class="absolute z-50 mt-1 w-full rounded-xl border border-zinc-200 bg-white p-3 text-sm text-zinc-500 shadow-lg dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
            Sin resultados para "{{ $query }}".
        </div>
    @endif
</div>
