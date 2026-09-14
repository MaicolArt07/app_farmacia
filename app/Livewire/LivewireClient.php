<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireClient extends Component
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
        $query = Client::with('person');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                  ->orWhere('nit', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhereHas('person', function ($personQuery) {
                      $personQuery->where('name', 'like', "%{$this->search}%")
                          ->orWhere('lastname', 'like', "%{$this->search}%")
                          ->orWhere('ci', 'like', "%{$this->search}%");
                  });
            });
        }

        return view('livewire.client.index', [
            'clients' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function delete($id)
    {
        try {
            $client = Client::findOrFail($id);
            $client->state = $client->state ? 0 : 1;
            $client->save();

            $this->dispatch(
                'toast',
                type: $client->state ? 'success' : 'warning',
                message: $client->state
                    ? 'Cliente activado correctamente.'
                    : 'Cliente desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'No se pudo actualizar el estado del cliente.');
        }
    }
}
