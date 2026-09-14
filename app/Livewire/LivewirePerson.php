<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Person;

class LivewirePerson extends Component
{
    use WithPagination;

    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $query = Person::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('lastname', 'like', "%{$this->search}%")
                  ->orWhere('ci', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.person.index', [
            'persons' => $query->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $person = Person::findOrFail($id);
            $person->state = $person->state ? 0 : 1;
            $person->save();

            $message = $person->state
                ? 'La persona fue activada correctamente.'
                : 'La persona fue desactivada correctamente.';

            $this->dispatch(
                'toast',
                type: $person->state ? 'success' : 'warning',
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
