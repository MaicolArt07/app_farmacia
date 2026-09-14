<?php

namespace App\Livewire;

use App\Models\CashRegister;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Form\CashRegisterForm;

class LivewireCashRegisterForm extends Component
{
    public CashRegisterForm $form;

    public function mount()
    {
        $this->form->resetForm();
    }

    public function save()
    {
        $this->validate($this->form->rulesOpen(), $this->form->messages());

        $hasOpenCash = CashRegister::where('id_user', Auth::id())
            ->where('status', 'OPEN')
            ->exists();

        if ($hasOpenCash) {
            $this->dispatch('toast', type: 'warning', message: 'Ya tiene una caja abierta.');
            return;
        }

        try {
            CashRegister::create([
                'id_user' => Auth::id(),
                'opening_date' => $this->form->opening_date,
                'opening_amount' => $this->form->opening_amount,
                'closing_amount' => null,
                'expected_amount' => $this->form->opening_amount,
                'difference' => null,
                'status' => 'OPEN',
                'state' => 1,
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
