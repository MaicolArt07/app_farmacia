<?php

namespace App\Livewire;

use App\Models\PaymentMethod;
use Livewire\Component;
use Livewire\WithPagination;

class LivewirePaymentMethod extends Component
{
    use WithPagination;

    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = PaymentMethod::query();

        if (!empty($this->search)) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        return view('livewire.payment-method.index', [
            'paymentMethods' => $query->orderBy('name')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $paymentMethod = PaymentMethod::findOrFail($id);
            $paymentMethod->state = $paymentMethod->state ? 0 : 1;
            $paymentMethod->save();

            $this->dispatch(
                'toast',
                type: $paymentMethod->state ? 'success' : 'warning',
                message: $paymentMethod->state
                    ? 'Método de pago activado correctamente.'
                    : 'Método de pago desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del método de pago.');
        }
    }
}
