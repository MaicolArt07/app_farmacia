<?php

namespace App\Livewire;

use App\Models\Caja;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Form\CajaForm;

class LivewireCajaForm extends Component
{
    public CajaForm $form;

    public function mount()
    {
        $this->form->resetForm();
    }

    public function save()
    {
        $this->validate($this->form->rulesOpen(), $this->form->messages());

        $hasOpenCash = Caja::where('id_user', Auth::id())
            ->where('status', 'OPEN')
            ->exists();

        if ($hasOpenCash) {
            $this->dispatch('toast', type: 'warning', message: 'Ya tiene una caja abierta.');
            return;
        }

        try {
            Caja::create([
                'id_user' => Auth::id(),
                'fecha_apertura' => now()->toDateString(),
                'monto_apertura' => $this->form->opening_amount,
                'periodo_validez' => $this->form->validity_period,
                'expires_at' => Caja::calculateExpiration($this->form->validity_period),
                'monto_cierre' => null,
                'monto_esperado' => $this->form->opening_amount,
                'diferencia' => null,
                'status' => 'OPEN',
                'estado' => 1,
            ]);

            $this->dispatch('toast', type: 'success', message: 'Caja abierta correctamente.');

            return redirect()->route('cash-registers');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo abrir la caja.');
        }
    }

    public function cancel()
    {
        return redirect()->route('cash-registers');
    }

    public function render()
    {
        return view('livewire.cash-register.form');
    }
}
