<?php

namespace App\Livewire;

use App\Models\Factura;
use App\Services\Facturacion\FacturacionServiceInterface;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Form\AnularFacturaForm;

class LivewireFactura extends Component
{
    use WithPagination;

    public $search = '';

    public $anularModalVisible = false;
    public $selectedInvoiceId = null;

    public AnularFacturaForm $anularForm;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Factura::with(['sale.client.person', 'user']);

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
        $this->anularForm->resetForm();
        $this->anularModalVisible = true;
    }

    public function closeAnularModal()
    {
        $this->selectedInvoiceId = null;
        $this->anularForm->resetForm();
        $this->anularModalVisible = false;
    }

    public function anular()
    {
        $this->validate($this->anularForm->rules(), $this->anularForm->messages());

        try {
            $invoice = Factura::findOrFail($this->selectedInvoiceId);

            if ($invoice->estado === 'ANULADA') {
                $this->dispatch('toast', type: 'warning', message: 'Esta factura ya está anulada.');
                $this->closeAnularModal();
                return;
            }

            app(FacturacionServiceInterface::class)->anular($invoice, $this->anularForm->anular_reason);

            $this->dispatch('toast', type: 'success', message: 'Factura anulada correctamente.');
            $this->closeAnularModal();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo anular la factura. ' . $e->getMessage());
        }
    }
}
