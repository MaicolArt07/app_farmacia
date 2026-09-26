<?php

namespace App\Livewire;

use App\Models\CashRegister;
use App\Models\CashMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LivewireCashRegister extends Component
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
    public $extendUntil = '';
    public $detailCash = null;
    public $closingCash = null;

    public $movement_type = 'INCOME';
    public $movement_concept = '';
    public $movement_amount = '';
    public $movement_observation = '';

    public $closing_amount = '';
    public $projectedDifference = 0;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = CashRegister::with('user');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('opening_date', 'like', "%{$this->search}%")
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
        $cash = CashRegister::findOrFail($id);

        if ($cash->status !== 'OPEN') {
            $this->dispatch('toast', type: 'warning', message: 'Solo puede registrar movimientos en una caja abierta.');
            return;
        }

        $this->selectedCashRegisterId = $id;
        $this->movement_type = 'INCOME';
        $this->movement_concept = '';
        $this->movement_amount = '';
        $this->movement_observation = '';

        $this->movementModalVisible = true;
    }

    public function saveMovement()
    {
        $this->validate([
            'movement_type' => 'required|in:INCOME,EXPENSE',
            'movement_concept' => 'required|string|max:150',
            'movement_amount' => 'required|numeric|min:0.01',
            'movement_observation' => 'nullable|string',
        ], [
            'movement_type.required' => 'Debe seleccionar el tipo de movimiento.',
            'movement_concept.required' => 'El concepto es obligatorio.',
            'movement_amount.required' => 'El monto es obligatorio.',
            'movement_amount.numeric' => 'El monto debe ser numérico.',
            'movement_amount.min' => 'El monto debe ser mayor a cero.',
        ]);

        DB::beginTransaction();

        try {
            $cash = CashRegister::where('id', $this->selectedCashRegisterId)
                ->where('status', 'OPEN')
                ->lockForUpdate()
                ->firstOrFail();

            CashMovement::create([
                'id_cash_register' => $cash->id,
                'id_user' => Auth::id(),
                'type' => $this->movement_type,
                'concept' => $this->movement_concept,
                'amount' => $this->movement_amount,
                'observation' => $this->movement_observation ?: null,
            ]);

            if ($this->movement_type === 'INCOME') {
                $cash->expected_amount += $this->movement_amount;
            } else {
                $cash->expected_amount -= $this->movement_amount;
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
        $this->movement_type = 'INCOME';
        $this->movement_concept = '';
        $this->movement_amount = '';
        $this->movement_observation = '';

        $this->movementModalVisible = false;
    }

    protected function loadCashWithDetails($id)
    {
        return CashRegister::with([
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
        $cash = CashRegister::findOrFail($id);

        if ($cash->status !== 'OPEN') {
            $this->dispatch('toast', type: 'warning', message: 'La caja ya está cerrada.');
            return;
        }

        $this->selectedCashRegisterId = $id;
        $this->closing_amount = '';
        $this->projectedDifference = 0 - (float) $cash->expected_amount;
        $this->closingCash = $this->loadCashWithDetails($id);

        $this->closeModalVisible = true;
    }

    public function updatedClosingAmount($value)
    {
        $expected = (float) ($this->closingCash?->expected_amount ?? 0);
        $this->projectedDifference = ($value === '' ? 0 : (float) $value) - $expected;
    }

    public function closeCashRegister()
    {
        $this->validate([
            'closing_amount' => 'required|numeric|min:0',
        ], [
            'closing_amount.required' => 'Debe ingresar el monto contado.',
            'closing_amount.numeric' => 'El monto contado debe ser numérico.',
        ]);

        DB::beginTransaction();

        try {
            $cash = CashRegister::where('id', $this->selectedCashRegisterId)
                ->where('status', 'OPEN')
                ->lockForUpdate()
                ->firstOrFail();

            $cash->closing_amount = $this->closing_amount;
            $cash->difference = $this->closing_amount - $cash->expected_amount;
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
        $this->closing_amount = '';
        $this->projectedDifference = 0;
        $this->closingCash = null;

        $this->closeModalVisible = false;
    }

    public function openExtendModal($id)
    {
        $cash = CashRegister::findOrFail($id);

        if ($cash->status !== 'OPEN') {
            $this->dispatch('toast', type: 'warning', message: 'Solo puede ampliar la vigencia de una caja abierta.');
            return;
        }

        $this->extendingCashRegisterId = $id;
        $this->extendingCash = $cash;
        $this->extendUntil = optional($cash->expires_at)->format('Y-m-d\TH:i')
            ?? CashRegister::calculateExpiration($cash->validity_period ?: 'DAILY')->format('Y-m-d\TH:i');

        $this->extendModalVisible = true;
    }

    public function setExtendPreset($period)
    {
        $this->extendUntil = CashRegister::calculateExpiration($period)->format('Y-m-d\TH:i');
    }

    public function confirmExtend()
    {
        $this->validate([
            'extendUntil' => 'required|date|after:now',
        ], [
            'extendUntil.required' => 'Debe indicar la nueva fecha final.',
            'extendUntil.after' => 'La fecha final debe ser posterior al momento actual.',
        ]);

        try {
            $cash = CashRegister::where('id', $this->extendingCashRegisterId)
                ->where('status', 'OPEN')
                ->firstOrFail();

            $cash->extendUntil($this->extendUntil);

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
        $this->extendUntil = '';

        $this->extendModalVisible = false;
    }

    public function delete($id)
    {
        try {
            $cash = CashRegister::findOrFail($id);

            if ($cash->status === 'OPEN') {
                $this->dispatch('toast', type: 'warning', message: 'No puede desactivar una caja abierta.');
                return;
            }

            $cash->state = $cash->state ? 0 : 1;
            $cash->save();

            $this->dispatch(
                'toast',
                type: $cash->state ? 'success' : 'warning',
                message: $cash->state
                    ? 'Caja activada correctamente.'
                    : 'Caja desactivada correctamente.'
            );

        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado de la caja.');
        }
    }
}
