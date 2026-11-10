{{--
    Contador de cortes (monedas y billetes de Bolivia). Obligatorio: el monto
    de la caja sale siempre de aquí, no se escribe a mano.

    Uso:
    @include('livewire.caja.partials.cortes', [
        'target' => 'openForm.opening_amount',  // propiedad Livewire que recibe el total
        'label'  => 'Monto inicial',            // texto del total
    ])

    Los montos se calculan en centavos (enteros) para evitar errores de redondeo.
    Estilos propios en <style> porque algunas variantes responsive no están en el
    build actual de Tailwind.
--}}
<style>
    .cortes-grid { display: grid; gap: 1rem; }
    .cortes-divider { height: 1px; background: #e4e4e7; }
    .dark .cortes-divider { background: #3f3f46; }
    @media (min-width: 640px) {
        .cortes-grid { grid-template-columns: 1fr 1px 1fr; gap: 1.25rem; }
        .cortes-divider { height: auto; }
    }
    .cortes-row { display: grid; grid-template-columns: 4.5rem 1fr 5rem; align-items: center; gap: .5rem; }
</style>

<div x-data="{
        target: @js($target),
        groups: [
            { title: 'Monedas', icon: 'bi-coin', list: [
                { c: 10,  l: '10 ctv' },
                { c: 20,  l: '20 ctv' },
                { c: 50,  l: '50 ctv' },
                { c: 100, l: '1 Bs' },
                { c: 200, l: '2 Bs' },
                { c: 500, l: '5 Bs' },
            ] },
            { title: 'Billetes', icon: 'bi-cash', list: [
                { c: 1000,  l: '10 Bs' },
                { c: 2000,  l: '20 Bs' },
                { c: 5000,  l: '50 Bs' },
                { c: 10000, l: '100 Bs' },
                { c: 20000, l: '200 Bs' },
            ] },
        ],
        qty: {},
        count(d) { const n = parseInt(this.qty[d.c]); return isNaN(n) || n < 0 ? 0 : n },
        sub(d) { return this.count(d) * d.c },
        sumOf(list) { return list.reduce((s, d) => s + this.sub(d), 0) },
        get totalCents() { return this.groups.reduce((s, g) => s + this.sumOf(g.list), 0) },
        get pieces() { return this.groups.reduce((s, g) => s + g.list.reduce((t, d) => t + this.count(d), 0), 0) },
        money(cents) { return (cents / 100).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
        sync() { $wire.set(this.target, this.totalCents > 0 ? (this.totalCents / 100).toFixed(2) : '') },
        clear() { this.qty = {}; this.sync() },
    }">

    <div class="mb-2 flex items-center justify-between">
        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">
            <i class="bi bi-cash-coin mr-1"></i>Cortes (monedas y billetes) <span class="text-red-600">*</span>
        </label>
        <button type="button" @click="clear()" x-show="pieces > 0"
            class="text-xs text-zinc-500 hover:text-red-600 dark:text-zinc-400 cursor-pointer">
            <i class="bi bi-eraser"></i> Limpiar
        </button>
    </div>

    <div class="rounded-lg border bg-gray-50 p-4 dark:bg-zinc-800/50
                {{ $errors->has($target) ? 'border-red-500' : 'border-gray-200 dark:border-zinc-700' }}">

        <div class="cortes-grid">
            <template x-for="(group, gi) in groups" :key="group.title">
                <div style="display: contents">
                    <div x-show="gi > 0" class="cortes-divider"></div>

                    <div>
                        <div class="mb-3 flex items-center justify-between border-b border-gray-300 pb-2 dark:border-zinc-700">
                            <span class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                                <i class="bi mr-1" :class="group.icon"></i><span x-text="group.title"></span>
                            </span>
                            <span class="text-sm font-semibold text-zinc-600 dark:text-zinc-300 tabular-nums"
                                x-text="'Bs ' + money(sumOf(group.list))"></span>
                        </div>

                        <div class="space-y-2">
                            <template x-for="d in group.list" :key="d.c">
                                <div class="cortes-row text-sm">
                                    <span class="font-medium text-zinc-700 dark:text-zinc-200" x-text="d.l"></span>
                                    <input type="number" min="0" step="1" inputmode="numeric" placeholder="0"
                                        x-model="qty[d.c]" @input.debounce.300ms="sync()"
                                        class="w-full rounded-md border border-gray-300 bg-white px-2 py-1 text-right dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                    <span class="text-right tabular-nums"
                                        :class="sub(d) > 0 ? 'text-zinc-800 dark:text-zinc-100' : 'text-zinc-400 dark:text-zinc-500'"
                                        x-text="money(sub(d))"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="mt-4 flex items-end justify-between border-t border-gray-300 pt-3 dark:border-zinc-700">
            <span class="text-xs text-zinc-500 dark:text-zinc-400" x-text="pieces + (pieces === 1 ? ' pieza' : ' piezas')"></span>
            <div class="text-right">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $label }}</div>
                <div class="text-xl font-bold text-zinc-800 dark:text-zinc-100">
                    Bs <span class="tabular-nums" x-text="money(totalCents)"></span>
                </div>
            </div>
        </div>
    </div>

    @error($target)
        <span class="text-sm text-red-500">{{ $message }}</span>
    @enderror
</div>
