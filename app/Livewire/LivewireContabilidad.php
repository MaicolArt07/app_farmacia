<?php

namespace App\Livewire;

use App\Models\GastoComun;
use App\Models\MovimientoContable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Form\GastoComunForm;

class LivewireContabilidad extends Component
{
    use WithPagination;

    public $tab = 'libro'; // libro | gastos

    public $mes; // formato Y-m

    // --- Gastos comunes (catálogo) ---
    public GastoComunForm $form;
    public $modalVisible = false;

    // --- Movimiento manual (Debe/Haber) ---
    public $movModalVisible = false;
    public $mov_tipo = 'DEBE';
    public $mov_concepto = '';
    public $mov_monto = '';
    public $mov_fecha = '';
    public $mov_id_gasto_comun = '';

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->mes = now()->format('Y-m');
    }

    public function updatingTab()
    {
        $this->resetPage();
    }

    public function render()
    {
        if ($this->tab === 'gastos') {
            return view('livewire.contabilidad.gastos', [
                'gastos' => GastoComun::orderBy('nombre')->paginate(10),
            ]);
        }

        $desde = \Illuminate\Support\Carbon::parse($this->mes . '-01')->startOfMonth();
        $hasta = (clone $desde)->endOfMonth();

        $query = MovimientoContable::with(['gastoComun', 'user'])->enPeriodo($desde, $hasta);

        $debe = (clone $query)->debe()->sum('monto');
        $haber = (clone $query)->haber()->sum('monto');

        return view('livewire.contabilidad.libro', [
            'movimientos' => (clone $query)->orderBy('fecha', 'desc')->orderBy('id', 'desc')->paginate(15),
            'debe' => $debe,
            'haber' => $haber,
            'saldo' => $haber - $debe,
            'gastosComunes' => GastoComun::where('estado', 1)->orderBy('nombre')->get(),
        ]);
    }

    /* ---------- Catálogo de gastos comunes ---------- */

    public function create()
    {
        $this->form->resetForm();
        $this->modalVisible = true;
    }

    public function edit($id)
    {
        $gasto = GastoComun::findOrFail($id);
        $this->form->fillForm($gasto);
        $this->modalVisible = true;
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            $data = [
                'nombre' => $this->form->nombre,
                'descripcion' => $this->form->descripcion ?: null,
                'monto_referencia' => $this->form->monto_referencia ?: null,
                'estado' => $this->form->estado,
            ];

            if ($this->form->id) {
                GastoComun::findOrFail($this->form->id)->update($data);
                $message = 'Gasto común actualizado correctamente.';
            } else {
                GastoComun::create($data);
                $message = 'Gasto común registrado correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);
            $this->modalVisible = false;
            $this->form->resetForm();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el gasto común.');
        }
    }

    public function cancel()
    {
        $this->modalVisible = false;
        $this->form->resetForm();
    }

    public function delete($id)
    {
        try {
            $gasto = GastoComun::findOrFail($id);
            $gasto->estado = $gasto->estado ? 0 : 1;
            $gasto->save();

            $this->dispatch(
                'toast',
                type: $gasto->estado ? 'success' : 'warning',
                message: $gasto->estado ? 'Gasto común activado correctamente.' : 'Gasto común desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del gasto común.');
        }
    }

    /* ---------- Libro diario: registrar movimiento (gasto del mes o manual) ---------- */

    public function openMovModal()
    {
        $this->mov_tipo = 'DEBE';
        $this->mov_concepto = '';
        $this->mov_monto = '';
        $this->mov_fecha = now()->format('Y-m-d');
        $this->mov_id_gasto_comun = '';
        $this->movModalVisible = true;
    }

    public function closeMovModal()
    {
        $this->movModalVisible = false;
        $this->resetErrorBag();
    }

    public function updatedMovIdGastoComun($value)
    {
        if (!$value) {
            return;
        }

        $gasto = GastoComun::find($value);

        if ($gasto) {
            $this->mov_concepto = $gasto->nombre;
            if ($gasto->monto_referencia) {
                $this->mov_monto = $gasto->monto_referencia;
            }
        }
    }

    public function saveMovimiento()
    {
        $this->validate([
            'mov_tipo' => 'required|in:DEBE,HABER',
            'mov_concepto' => 'required|string|max:255',
            'mov_monto' => 'required|numeric|min:0.01',
            'mov_fecha' => 'required|date',
        ], [
            'mov_concepto.required' => 'El concepto es obligatorio.',
            'mov_monto.required' => 'El monto es obligatorio.',
            'mov_monto.numeric' => 'El monto debe ser numérico.',
            'mov_monto.min' => 'El monto debe ser mayor a cero.',
            'mov_fecha.required' => 'La fecha es obligatoria.',
        ]);

        try {
            MovimientoContable::create([
                'fecha' => $this->mov_fecha,
                'tipo' => $this->mov_tipo,
                'concepto' => $this->mov_concepto,
                'monto' => $this->mov_monto,
                'origen' => $this->mov_id_gasto_comun ? 'GASTO_COMUN' : 'MANUAL',
                'id_gasto_comun' => $this->mov_id_gasto_comun ?: null,
                'id_usuario' => Auth::id(),
            ]);

            $this->dispatch('toast', type: 'success', message: 'Movimiento registrado correctamente.');
            $this->closeMovModal();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo registrar el movimiento.');
        }
    }

    public function deleteMovimiento($id)
    {
        try {
            $mov = MovimientoContable::findOrFail($id);

            if (in_array($mov->origen, ['COMPRA', 'CIERRE_CAJA'], true)) {
                $this->dispatch('toast', type: 'warning', message: 'Este movimiento es automático (compra o cierre de caja) y no se puede eliminar aquí.');
                return;
            }

            $mov->delete();
            $this->dispatch('toast', type: 'success', message: 'Movimiento eliminado correctamente.');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo eliminar el movimiento.');
        }
    }
}
