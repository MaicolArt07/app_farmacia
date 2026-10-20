<?php

namespace App\Livewire;

use App\Models\MetodoPago;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Form\MetodoPagoForm;

class LivewireMetodoPago extends Component
{
    use WithPagination;

    public $search = '';

    public MetodoPagoForm $form;

    public $modalVisible = false;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = MetodoPago::query();

        if (!empty($this->search)) {
            $query->where('nombre', 'like', "%{$this->search}%");
        }

        return view('livewire.metodo-pago.index', [
            'paymentMethods' => $query->orderBy('nombre')->paginate(10),
        ]);
    }

    public function create()
    {
        $this->form->resetForm();
        $this->modalVisible = true;
    }

    public function edit($id)
    {
        $paymentMethod = MetodoPago::findOrFail($id);
        $this->form->fillForm($paymentMethod);
        $this->modalVisible = true;
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        try {
            $data = [
                'nombre' => $this->form->nombre,
                'codigo_sin' => $this->form->codigo_sin ?: null,
                'estado' => $this->form->estado,
            ];

            if ($this->form->id) {
                $paymentMethod = MetodoPago::findOrFail($this->form->id);
                $paymentMethod->update($data);
                $message = 'Método de pago actualizado correctamente.';
            } else {
                MetodoPago::create($data);
                $message = 'Método de pago registrado correctamente.';
            }

            $this->dispatch('toast', type: 'success', message: $message);
            $this->modalVisible = false;
            $this->form->resetForm();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo guardar el método de pago.');
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
            $paymentMethod = MetodoPago::findOrFail($id);
            $paymentMethod->estado = $paymentMethod->estado ? 0 : 1;
            $paymentMethod->save();

            $this->dispatch(
                'toast',
                type: $paymentMethod->estado ? 'success' : 'warning',
                message: $paymentMethod->estado
                    ? 'Método de pago activado correctamente.'
                    : 'Método de pago desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del método de pago.');
        }
    }
}
