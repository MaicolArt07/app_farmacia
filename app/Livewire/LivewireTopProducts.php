<?php

namespace App\Livewire;

use App\Support\TopProductsReport;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Reporte "Productos más vendidos" (solo administrador).
 * Primero se filtra y se ve en pantalla; recién entonces se puede exportar
 * a PDF o Excel con las mismas fechas consultadas.
 */
class LivewireTopProducts extends Component
{
    public $from;
    public $to;
    public $order = 'cantidad';

    /** Fechas ya consultadas (las que usa la exportación). */
    #[Locked] public $shownFrom = null;
    #[Locked] public $shownTo = null;
    #[Locked] public $shownOrder = 'cantidad';

    public function mount()
    {
        abort_unless(Auth::user()?->hasRole('admin'), 403);

        $this->from = now()->startOfMonth()->toDateString();
        $this->to = now()->toDateString();
    }

    public function generate()
    {
        abort_unless(Auth::user()?->hasRole('admin'), 403);

        $this->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'order' => 'required|in:' . implode(',', array_keys(TopProductsReport::ORDERS)),
        ], [
            'from.required' => 'Indique la fecha de inicio.',
            'to.required' => 'Indique la fecha final.',
            'to.after_or_equal' => 'La fecha final no puede ser anterior a la de inicio.',
        ]);

        $this->shownFrom = $this->from;
        $this->shownTo = $this->to;
        $this->shownOrder = $this->order;
    }

    /** Si cambian el filtro, hay que volver a consultar antes de exportar. */
    public function updated($field)
    {
        if (in_array($field, ['from', 'to', 'order'], true)) {
            $this->resetErrorBag($field);
        }
    }

    public function render()
    {
        $rows = collect();
        $totals = null;

        if ($this->shownFrom && $this->shownTo) {
            $rows = TopProductsReport::rows($this->shownFrom, $this->shownTo, $this->shownOrder);
            $totals = TopProductsReport::totals($rows);
        }

        $dirty = $this->shownFrom && (
            $this->from !== $this->shownFrom || $this->to !== $this->shownTo || $this->order !== $this->shownOrder
        );

        return view('livewire.reportes.top-products', [
            'rows' => $rows,
            'totals' => $totals,
            'dirty' => $dirty,
            'orders' => TopProductsReport::ORDERS,
        ]);
    }
}
