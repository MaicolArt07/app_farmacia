<?php

namespace App\Livewire;

use App\Models\Invoice;
use App\Services\Facturacion\FacturacionServiceInterface;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireInvoice extends Component
{
    use WithPagination;

    public $search = '';

    public $anularModalVisible = false;
    public $selectedInvoiceId = null;
    public $anular_reason = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Invoice::with(['sale.client.person', 'user']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('numero_factura', 'like', "%{$this->search}%")
                    ->orWhere('nit_cliente', 'like', "%{$this->search}%")
                    ->orWhere('razon_social', 'like', "%{$this->search}%")
                    ->orWhere('cuf', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.invoice.index', [
            'invoices' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function openAnularModal($id)
    {
        $this->selectedInvoiceId = $id;
        $this->anular_reason = '';
        $this->anularModalVisible = true;
    }

    public function closeAnularModal()
    {
        $this->selectedInvoiceId = null;
        $this->anular_reason = '';
        $this->anularModalVisible = false;
    }

    public function anular()
    {
        $this->validate([
            'anular_reason' => 'required|string|max:255',
        ], [
            'anular_reason.required' => 'Debe indicar el motivo de anulación.',
        ]);

        try {
            $invoice = Invoice::findOrFail($this->selectedInvoiceId);

            if ($invoice->estado === 'ANULADA') {
                $this->dispatch('toast', type: 'warning', message: 'Esta factura ya está anulada.');
                $this->closeAnularModal();
                return;
            }

            app(FacturacionServiceInterface::class)->anular($invoice, $this->anular_reason);

            $this->dispatch('toast', type: 'success', message: 'Factura anulada correctamente.');
            $this->closeAnularModal();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo anular la factura. ' . $e->getMessage());
        }
    }
}
