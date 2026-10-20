<?php

namespace App\Livewire;

use App\Models\Caja;
use App\Models\MovimientoCaja;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Form\MovimientoCajaForm;
use App\Livewire\Form\CierreCajaForm;
use App\Livewire\Form\ExtenderCajaForm;

class LivewireCaja extends Component
{
    use WithPagination;

    public $search = '';
    public $movementModalVisible = false;
    public $closeModalVisible = false;
    public $extendModalVisible = false;
    public $detailModalVisible = false;

    public $selectedCashRegisterId = null;
    public $extendingCashRegisterId = null;
    public $extendingCash = null;
    public $detailCash = null;
    public $closingCash = null;

    public MovimientoCajaForm $movementForm;
    public CierreCajaForm $closeForm;
    public ExtenderCajaForm $extendForm;

    public $projectedDifference = 0;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Caja::with('user');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('fecha_apertura', 'like', "%{$this->search}%")
                  ->orWhere('status', 'like', "%{$this->search}%")
                  ->orWhereHas('user', function ($sub) {
                      $sub->where('name', 'like', "%{$this->search}%");
                  });
            });
        }

        return view('livewire.cash-register.index', [
            'cashRegisters' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function openMovementModal($id)
    {
        $cash = Caja::findOrFail($id);

        if ($cash->status !== 'OPEN') {
            $this->dispatch('toast', type: 'warning', message: 'Solo puede registrar movimientos en una caja abierta.');
            return;
        }

        $this->selectedCashRegisterId = $id;
        $this->movementForm->resetForm();

        $this->movementModalVisible = true;
    }

    public function saveMovement()
    {
        $this->validate($this->movementForm->rules(), $this->movementForm->messages());

        DB::beginTransaction();

        try {
            $cash = Caja::where('id', $this->selectedCashRegisterId)
                ->where('status', 'OPEN')
                ->lockForUpdate()
                ->firstOrFail();

            MovimientoCaja::create([
                'id_cash_register' => $cash->id,
                'id_user' => Auth::id(),
                'type' => $this->movementForm->type,
                'concepto' => $this->movementForm->concepto,
                'monto' => $this->movementForm->monto,
                'observacion' => $this->movementForm->observacion ?: null,
            ]);

            if ($this->movementForm->type === 'INCOME') {
                $cash->monto_esperado += $this->movementForm->monto;
            } else {
                $cash->monto_esperado -= $this->movementForm->monto;
            }

            $cash->save();

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Movimiento registrado correctamente.');
            $this->closeMovementModal();

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo registrar el movimiento.');
        }
    }

    public function closeMovementModal()
    {
        $this->resetValidation();
        $this->resetErrorBag();

        $this->selectedCashRegisterId = null;
        $this->movementForm->resetForm();

        $this->movementModalVisible = false;
    }

    protected function loadCashWithDetails($id)
    {
        return Caja::with([
            'user',
            'sales' => fn ($q) => $q->orderByDesc('id'),
            'movements' => fn ($q) => $q->orderByDesc('id'),
        ])->findOrFail($id);
    }

    public function openDetailModal($id)
    {
        $this->detailCash = $this->loadCashWithDetails($id);
        $this->detailModalVisible = true;
    }

    public function closeDetailModal()
    {
        $this->detailCash = null;
        $this->detailModalVisible = false;
    }

    public function openCloseCashModal($id)
    {
        $cash = Caja::findOrFail($id);

        if ($cash->status !== 'OPEN') {
            $this->dispatch('toast', type: 'warning', message: 'La caja ya está cerrada.');
            return;
        }

        $this->selectedCashRegisterId = $id;
        $this->closeForm->resetForm();
        $this->projectedDifference = 0 - (float) $cash->monto_esperado;
        $this->closingCash = $this->loadCashWithDetails($id);

        $this->closeModalVisible = true;
    }

    public function updatedCloseFormClosingAmount($value)
    {
        $expected = (float) ($this->closingCash?->monto_esperado ?? 0);
        $this->projectedDifference = ($value === '' ? 0 : (float) $value) - $expected;
    }

    public function closeCashRegister()
    {
        $this->validate($this->closeForm->rules(), $this->closeForm->messages());

        DB::beginTransaction();

        try {
            $cash = Caja::where('id', $this->selectedCashRegisterId)
                ->where('status', 'OPEN')
                ->lockForUpdate()
                ->firstOrFail();

            $cash->monto_cierre = $this->closeForm->closing_amount;
            $cash->diferencia = $this->closeForm->closing_amount - $cash->monto_esperado;
            $cash->status = 'CLOSED';
            $cash->save();

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Caja cerrada correctamente.');
            $this->closeCloseCashModal();

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'No se pudo cerrar la caja.');
        }
    }

    public function closeCloseCashModal()
    {
        $this->resetValidation();
        $this->resetErrorBag();

        $this->selectedCashRegisterId = null;
        $this->closeForm->resetForm();
        $this->projectedDifference = 0;
        $this->closingCash = null;

        $this->closeModalVisible = false;
    }

    public function openExtendModal($id)
    {
        $cash = Caja::findOrFail($id);

        if ($cash->status !== 'OPEN') {
            $this->dispatch('toast', type: 'warning', message: 'Solo puede ampliar la vigencia de una caja abierta.');
            return;
        }

        $this->extendingCashRegisterId = $id;
        $this->extendingCash = $cash;
        $this->extendForm->extendUntil = optional($cash->expires_at)->format('Y-m-d\TH:i')
            ?? Caja::calculateExpiration($cash->periodo_validez ?: 'DAILY')->format('Y-m-d\TH:i');

        $this->extendModalVisible = true;
    }

    public function setExtendPreset($period)
    {
        $this->extendForm->extendUntil = Caja::calculateExpiration($period)->format('Y-m-d\TH:i');
    }

    public function confirmExtend()
    {
        $this->validate($this->extendForm->rules(), $this->extendForm->messages());

        try {
            $cash = Caja::where('id', $this->extendingCashRegisterId)
                ->where('status', 'OPEN')
                ->firstOrFail();

            $cash->extendUntil($this->extendForm->extendUntil);

            $this->dispatch('toast', type: 'success', message: 'Vigencia de la caja ampliada correctamente.');
            $this->closeExtendModal();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo ampliar la vigencia de la caja.');
        }
    }

    public function closeExtendModal()
    {
        $this->resetValidation();
        $this->resetErrorBag();

        $this->extendingCashRegisterId = null;
        $this->extendingCash = null;
        $this->extendForm->resetForm();

        $this->extendModalVisible = false;
    }

    public function delete($id)
    {
        try {
            $cash = Caja::findOrFail($id);

            if ($cash->status === 'OPEN') {
                $this->dispatch('toast', type: 'warning', message: 'No puede desactivar una caja abierta.');
                return;
            }

            $cash->estado = $cash->estado ? 0 : 1;
            $cash->save();

            $this->dispatch(
                'toast',
                type: $cash->estado ? 'success' : 'warning',
                message: $cash->estado
                    ? 'Caja activada correctamente.'
                    : 'Caja desactivada correctamente.'
            );

        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado de la caja.');
        }
    }
}
