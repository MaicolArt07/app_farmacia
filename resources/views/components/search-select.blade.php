@props([
    'options' => [],
    'model',
    'placeholder' => 'Seleccione',
    'value' => null,
])

<div
    x-data="{
        open: false,
        search: '',
        options: @js($options),
        selected: @js($value),
        get filtered() {
            const q = this.search.toLowerCase();
            return this.options.filter(o => o.label.toLowerCase().includes(q));
        },
        get selectedLabel() {
            const found = this.options.find(o => String(o.value) === String(this.selected));
            return found ? found.label : '';
        },
        select(opt) {
            this.selected = opt.value;
            $wire.set(@js($model), opt.value);
            this.open = false;
            this.search = '';
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.search = '';
                this.$nextTick(() => this.$refs.searchInput?.focus());
            }
        }
    }"
    x-on:click.outside="open = false"
    class="relative"
>
    <button
        type="button"
        @click="toggle()"
        class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-zinc-800 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
    >
        <span x-text="selectedLabel || '{{ $placeholder }}'" :class="!selectedLabel ? 'text-gray-400 dark:text-zinc-500' : ''"></span>
        <i class="bi bi-chevron-down text-xs text-zinc-400"></i>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition
        class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
    >
        <div class="p-2">
            <input
                type="text"
                x-ref="searchInput"
                x-model="search"
                placeholder="Buscar..."
                class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm text-zinc-800 focus:border-blue-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                @click.stop
            >
        </div>

        <div class="max-h-52 overflow-y-auto">
            <template x-for="opt in filtered" :key="opt.value">
                <button
                    type="button"
                    @click="select(opt)"
                    class="block w-full px-3 py-2 text-left text-sm text-zinc-700 hover:bg-gray-100 dark:text-zinc-100 dark:hover:bg-zinc-700"
                    x-text="opt.label"
                ></button>
            </template>

            <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-400 dark:text-zinc-500">
                Sin resultados
            </div>
        </div>
    </div>
</div>
