<div class="bg-zinc-200 p-4 dark:bg-zinc-950">
    <div class="mb-3 flex flex-col gap-3 rounded border border-zinc-300 bg-zinc-100 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900 md:flex-row md:items-center md:justify-between">
        <h1 class="text-xl font-bold text-zinc-800 dark:text-zinc-100">
            Facturación
        </h1>

        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por N° factura, NIT, razón social o CUF..."
            class="w-full rounded border border-zinc-400 bg-white px-3 py-1.5 text-sm text-zinc-800 focus:border-primary focus:outline-none md:w-96 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        >
    </div>

    <div class="overflow-auto rounded border border-zinc-300 dark:border-zinc-700">
        <table class="min-w-full border-collapse text-sm">
            <thead class="sticky top-0 bg-zinc-300 dark:bg-zinc-800">
                <tr>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">N° FACTURA</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">VENTA</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-left font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">CLIENTE / NIT</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-right font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">TOTAL</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">MODO</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">ESTADO</th>
                    <th class="border-b border-zinc-400 px-3 py-1.5 text-center font-semibold text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">OPCIONES</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900">
                @forelse ($invoices as $invoice)
                    <tr class="border-b border-zinc-200 hover:bg-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $invoice->numero_factura ?? '-' }}
                        </td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            Venta #{{ $invoice->id_sale }}
                        </td>
                        <td class="px-3 py-1.5 text-zinc-800 dark:text-zinc-100">
                            {{ $invoice->razon_social }}
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">NIT/CI: {{ $invoice->nit_cliente }}</div>
                        </td>
                        <td class="px-3 py-1.5 text-right text-zinc-800 dark:text-zinc-100">
                            {{ number_format($invoice->total, 2) }}
                        </td>
                        <td class="px-3 py-1.5 text-center">
                            @if ($invoice->isSimulado())
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">Simulado</span>
                            @else
                                <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-medium text-sky-700 dark:bg-sky-900/30 dark:text-sky-300">Real</span>
                            @endif
                        </td>
                        <td class="px-3 py-1.5 text-center">
                            @php
                                $invoiceBadgeClasses = match ($invoice->estado) {
                                    'ENVIADA' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                    'OBSERVADA' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                    'ERROR' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                    'ANULADA' => 'bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
                                    default => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                };
                            @endphp
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $invoiceBadgeClasses }}">
                                {{ $invoice->estado_label }}
                            </span>
                        </td>
                        <td class="px-3 py-1.5">
                            <div class="flex justify-center gap-3">
                                <a href="{{ route('invoices.pdf', $invoice->id) }}" target="_blank"
                                    class="cursor-pointer text-zinc-500 transition hover:text-primary dark:text-zinc-400"
                                    title="Ver / reimprimir">
                                    <i class="bi bi-printer text-base"></i>
                                </a>

                                @can('Cambiar Estado Facturas')
                                    @if ($invoice->estado !== 'ANULADA')
                                        <button wire:click="openAnularModal({{ $invoice->id }})"
                                            class="cursor-pointer text-zinc-500 transition hover:text-red-600 dark:text-zinc-400"
                                            title="Anular factura">
                                            <i class="bi bi-x-circle text-base"></i>
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            No hay facturas generadas todavía.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 dark:text-zinc-200">
        {{ $invoices->links() }}
    </div>

    @if ($anularModalVisible)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-zinc-800">
                    <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">Anular factura</h2>
                    <button wire:click="closeAnularModal" class="text-gray-500 hover:text-red-500 dark:text-zinc-400">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="space-y-4 px-6 py-5">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Motivo de anulación</label>
                        <textarea wire:model="anularForm.anular_reason" rows="3"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                        @error('anularForm.anular_reason')
                            <span class="text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
                    <button wire:click="closeAnularModal"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-zinc-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                        Cancelar
                    </button>
                    <button wire:click="anular"
                        class="rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600">
                        Anular
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
