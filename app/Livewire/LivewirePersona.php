<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Persona;

class LivewirePersona extends Component
{
    use WithPagination;

    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $query = Persona::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('apellido', 'like', "%{$this->search}%")
                  ->orWhere('ci', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.persona.index', [
            'persons' => $query->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $person = Persona::findOrFail($id);
            $person->estado = $person->estado ? 0 : 1;
            $person->save();

            $message = $person->estado
                ? 'La persona fue activada correctamente.'
                : 'La persona fue desactivada correctamente.';

            $this->dispatch(
                'toast',
                type: $person->estado ? 'success' : 'warning',
                message: $message
            );
        } catch (\Throwable $e) {
            $this->dispatch(
                'toast',
                type: 'error',
                message: 'No se pudo actualizar el estado de la persona.'
            );
        }
    }
}
