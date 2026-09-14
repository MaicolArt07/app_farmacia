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

    public $selectedCashRegisterId = null;

    public $movement_type = 'INCOME';
    public $movement_concept = '';
    public $movement_amount = '';
    public $movement_observation = '';

    public $closing_amount = '';

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

    public function openCloseCashModal($id)
    {
        $cash = CashRegister::findOrFail($id);

        if ($cash->status !== 'OPEN') {
            $this->dispatch('toast', type: 'warning', message: 'La caja ya está cerrada.');
            return;
        }

        $this->selectedCashRegisterId = $id;
        $this->closing_amount = '';

        $this->closeModalVisible = true;
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

        $this->closeModalVisible = false;
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
