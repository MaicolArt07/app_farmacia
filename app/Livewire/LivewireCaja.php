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
use App\Livewire\Form\CajaForm;

class LivewireCaja extends Component
{
    use WithPagination;

    public $search = '';
    public $openModalVisible = false;
    public $movementModalVisible = false;
    public $closeModalVisible = false;
    public $extendModalVisible = false;
    public $detailModalVisible = false;

    public $selectedCashRegisterId = null;
    public $extendingCashRegisterId = null;
    public $extendingCash = null;
    public $detailCash = null;
    public $closingCash = null;

    public CajaForm $openForm;
    public MovimientoCajaForm $movementForm;
    public CierreCajaForm $closeForm;
    public ExtenderCajaForm $extendForm;

    public $projectedDifference = 0;

    /** Cierre en dos verificaciones: 1 = QR, 2 = efectivo (cortes). */
    public $closeStep = 1;
    public $qrExpected = 0;
    public $qrDifference = 0;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * La caja es por usuario (turno mañana / turno tarde): cada usuario abre,
     * cuenta y cierra su propia caja. El administrador puede ver y gestionar
     * todas (p. ej. cerrar una caja que el otro turno olvidó cerrar).
     */
    protected function isAdmin(): bool
    {
        return Auth::user()?->hasRole('admin') ?? false;
    }

    /**
     * Verifica el permiso también en el servidor (no solo ocultando botones).
     * Trabajador: solo Ver/Crear/Cerrar Caja.
     */
    protected function allowed(string $permission): bool
    {
        if (Auth::user()?->can($permission)) {
            return true;
        }

        $this->dispatch('toast', type: 'error', message: 'No tiene permiso para realizar esta acción.');

        return false;
    }

    protected function visibleCashQuery()
    {
        return Caja::query()->when(!$this->isAdmin(), fn ($q) => $q->where('id_usuario', Auth::id()));
    }

    protected function findVisibleCash($id): Caja
    {
        return $this->visibleCashQuery()->findOrFail($id);
    }

    public function render()
    {
        $query = $this->visibleCashQuery()->with('user');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('fecha_apertura', 'like', "%{$this->search}%")
                  ->orWhere('status', 'like', "%{$this->search}%")
                  ->orWhereHas('user', function ($sub) {
                      $sub->where('name', 'like', "%{$this->search}%");
                  });
            });
        }

        // Total general (todas las cajas que coinciden con el filtro, no solo
        // la página): ventas en efectivo y QR, sin el monto inicial.
        $cajaIds = (clone $query)->pluck('id');
        $sold = \App\Models\Venta::whereIn('id_caja', $cajaIds)->where('status', 'ACTIVE');
        $grandTotals = [
            'efectivo' => (float) (clone $sold)->sum(DB::raw(\App\Models\Venta::SQL_CASH)),
            'qr' => (float) (clone $sold)->sum(DB::raw(\App\Models\Venta::SQL_QR)),
            'cajas' => $cajaIds->count(),
        ];

        // Ventas de cada caja separadas en efectivo / QR (Mixto se reparte),
        // solo ventas activas.
        $query->addSelect([
            'ventas_efectivo' => \App\Models\Venta::selectRaw('COALESCE(SUM(' . \App\Models\Venta::SQL_CASH . '), 0)')
                ->whereColumn('ventas.id_caja', 'cajas.id')->where('status', 'ACTIVE'),
            'ventas_qr' => \App\Models\Venta::selectRaw('COALESCE(SUM(' . \App\Models\Venta::SQL_QR . '), 0)')
                ->whereColumn('ventas.id_caja', 'cajas.id')->where('status', 'ACTIVE'),
        ]);

        return view('livewire.caja.index', [
            'cashRegisters' => $query->orderBy('id', 'desc')->paginate(10),
            'grandTotals' => $grandTotals,
        ]);
    }

    public function openOpenModal()
    {
        $this->openForm->resetForm();
        $this->openModalVisible = true;
    }

    public function closeOpenModal()
    {
        $this->resetValidation();
        $this->resetErrorBag();

        $this->openForm->resetForm();
        $this->openModalVisible = false;
    }

    public function saveOpen()
    {
        if (!$this->allowed('Crear Caja')) return;

        $this->validate($this->openForm->rulesOpen(), $this->openForm->messages());

        $hasOpenCash = Caja::where('id_usuario', Auth::id())
            ->where('status', 'OPEN')
            ->exists();

        if ($hasOpenCash) {
            $this->dispatch('toast', type: 'warning', message: 'Ya tiene una caja abierta.');
            return;
        }

        try {
            Caja::create([
                'id_usuario' => Auth::id(),
                'fecha_apertura' => now()->toDateString(),
                'monto_apertura' => $this->openForm->opening_amount,
                // La caja siempre es del día: vence al final del día de apertura.
                'periodo_validez' => 'DAILY',
                'expires_at' => now()->endOfDay(),
                'monto_cierre' => null,
                'monto_esperado' => $this->openForm->opening_amount,
                'diferencia' => null,
                'status' => 'OPEN',
                'estado' => 1,
            ]);

            $this->dispatch('toast', type: 'success', message: 'Caja abierta correctamente.');
            $this->closeOpenModal();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo abrir la caja.');
        }
    }

    public function openMovementModal($id)
    {
        if (!$this->allowed('Crear Movimientos Caja')) return;

        $cash = $this->findVisibleCash($id);

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
        if (!$this->allowed('Crear Movimientos Caja')) return;

        $this->validate($this->movementForm->rules(), $this->movementForm->messages());

        DB::beginTransaction();

        try {
            $cash = $this->visibleCashQuery()
                ->where('id', $this->selectedCashRegisterId)
                ->where('status', 'OPEN')
                ->lockForUpdate()
                ->firstOrFail();

            MovimientoCaja::create([
                'id_caja' => $cash->id,
                'id_usuario' => Auth::id(),
                'type' => $this->movementForm->type,
                'concepto' => $this->movementForm->concepto,
                'monto' => $this->movementForm->monto,
                'observacion' => $this->movementForm->observacion ?: null,
            ]);

            $cash->recalculateExpected();

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
        return $this->visibleCashQuery()->with([
            'user',
            'sales' => fn ($q) => $q->orderByDesc('id'),
            'movements' => fn ($q) => $q->orderByDesc('id'),
        ])->findOrFail($id);
    }

    public function openDetailModal($id)
    {
        if (!$this->allowed('Ver Movimientos Caja')) return;

        $this->detailCash = $this->loadCashWithDetails($id);
        $this->detailModalVisible = true;
    }

    public function closeDetailModal()
    {
        $this->detailCash = null;
        $this->detailModalVisible = false;
    }

    /* ---------- Observaciones del admin sobre una caja ---------- */

    public $noteModalVisible = false;
    public $noteCashId = null;
    public $noteText = '';

    public function openNoteModal($id)
    {
        if (!$this->isAdmin()) {
            $this->dispatch('toast', type: 'error', message: 'Solo el administrador puede escribir observaciones.');
            return;
        }

        $cash = $this->findVisibleCash($id);
        $this->noteCashId = $cash->id;
        $this->noteText = (string) $cash->observacion;
        $this->resetErrorBag('noteText');
        $this->noteModalVisible = true;
    }

    public function saveNote()
    {
        if (!$this->isAdmin()) return;

        $this->validate(
            ['noteText' => 'nullable|string|max:5000'],
            ['noteText.max' => 'La observación no puede pasar de 5000 caracteres.']
        );

        $cash = $this->findVisibleCash($this->noteCashId);
        $cash->observacion = trim($this->noteText) === '' ? null : trim($this->noteText);
        $cash->save();

        $this->dispatch('toast', type: 'success', message: 'Observación guardada.');
        $this->closeNoteModal();
    }

    public function closeNoteModal()
    {
        $this->noteModalVisible = false;
        $this->noteCashId = null;
        $this->noteText = '';
    }

    public function openCloseCashModal($id)
    {
        if (!$this->allowed('Cerrar Caja')) return;

        $cash = $this->findVisibleCash($id);

        if ($cash->status !== 'OPEN') {
            $this->dispatch('toast', type: 'warning', message: 'La caja ya está cerrada.');
            return;
        }

        // Esperado = solo EFECTIVO (el QR no se cuenta en los cortes).
        $cash->recalculateExpected();

        $this->selectedCashRegisterId = $id;
        $this->closeForm->resetForm();
        $this->projectedDifference = 0 - (float) $cash->monto_esperado;
        $this->closingCash = $this->loadCashWithDetails($id);

        // Paso 1: verificación QR.
        $this->closeStep = 1;
        $this->qrExpected = $cash->cashBreakdown()['qr_sales'];
        $this->qrDifference = 0 - $this->qrExpected;

        $this->closeModalVisible = true;
    }

    public function updatedCloseFormQrAmount($value)
    {
        $this->resetErrorBag('closeForm.qr_amount');
        $this->qrDifference = round((is_numeric($value) ? (float) $value : 0) - (float) $this->qrExpected, 2);
    }

    /** Botón "Coincide": el QR de la cuenta es igual al del sistema. */
    public function qrMatches()
    {
        $this->closeForm->qr_amount = number_format((float) $this->qrExpected, 2, '.', '');
        $this->updatedCloseFormQrAmount($this->closeForm->qr_amount);
    }

    /** Paso 1 -> 2: valida el QR y pasa a contar el efectivo. */
    public function verifyQr()
    {
        if (!$this->allowed('Cerrar Caja')) return;

        $this->validate($this->closeForm->qrRules(), $this->closeForm->messages());
        $this->updatedCloseFormQrAmount($this->closeForm->qr_amount);
        $this->closeStep = 2;
    }

    public function backToQr()
    {
        $this->closeStep = 1;
    }

    public function updatedOpenFormOpeningAmount()
    {
        $this->resetErrorBag('openForm.opening_amount');
    }

    public function updatedCloseFormClosingAmount($value)
    {
        $this->resetErrorBag('closeForm.closing_amount');

        $expected = (float) ($this->closingCash?->monto_esperado ?? 0);
        $this->projectedDifference = ($value === '' ? 0 : (float) $value) - $expected;
    }

    public function closeCashRegister()
    {
        if (!$this->allowed('Cerrar Caja')) return;

        // Primero el QR; si no se verificó, vuelve al paso 1.
        try {
            $this->validate($this->closeForm->qrRules(), $this->closeForm->messages());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->closeStep = 1;
            throw $e;
        }
        $this->validate($this->closeForm->rules(), $this->closeForm->messages());

        DB::beginTransaction();

        try {
            $cash = $this->visibleCashQuery()
                ->where('id', $this->selectedCashRegisterId)
                ->where('status', 'OPEN')
                ->lockForUpdate()
                ->firstOrFail();

            $breakdown = $cash->cashBreakdown();

            // Verificación 1: QR (cuenta bancaria).
            $cash->monto_qr_esperado = $breakdown['qr_sales'];
            $cash->monto_qr_verificado = (float) $this->closeForm->qr_amount;
            $cash->diferencia_qr = round($cash->monto_qr_verificado - $breakdown['qr_sales'], 2);

            // Verificación 2: efectivo (cortes).
            $cash->monto_esperado = $breakdown['expected_cash'];
            $cash->monto_cierre = $this->closeForm->closing_amount;
            $cash->diferencia = $this->closeForm->closing_amount - $cash->monto_esperado;
            $cash->status = 'CLOSED';
            $cash->closed_at = now();
            $cash->save();

            // Contabilidad: el cierre registra lo vendido como HABER (ingreso).
            if ($breakdown['total_sales'] > 0) {
                \App\Models\MovimientoContable::registrarAutomatico('CIERRE_CAJA', $cash->id, [
                    'fecha' => $cash->closed_at->toDateString(),
                    'tipo' => \App\Models\MovimientoContable::HABER,
                    'concepto' => 'Cierre de caja #' . $cash->id . ' - ' . ($cash->user?->name ?? 'Usuario'),
                    'monto' => $breakdown['total_sales'],
                    'id_usuario' => auth()->id(),
                ]);
            }

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
        $this->closeStep = 1;
        $this->qrExpected = 0;
        $this->qrDifference = 0;

        $this->closeModalVisible = false;
    }

    public function openExtendModal($id)
    {
        if (!$this->allowed('Editar Caja')) return;

        $cash = $this->findVisibleCash($id);

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
        if (!$this->allowed('Editar Caja')) return;

        $this->validate($this->extendForm->rules(), $this->extendForm->messages());

        try {
            $cash = $this->visibleCashQuery()
                ->where('id', $this->extendingCashRegisterId)
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
        if (!$this->allowed('Cambiar Estado Caja')) return;

        try {
            $cash = $this->findVisibleCash($id);

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
