<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Product;
use Livewire\Component;

class LivewireGlobalSearch extends Component
{
    public $query = '';
    public $clients = [];
    public $products = [];
    public $open = false;

    public function updatedQuery()
    {
        $term = trim($this->query);

        if (strlen($term) < 2) {
            $this->clients = [];
            $this->products = [];
            $this->open = false;
            return;
        }

        $this->open = true;

        $this->clients = auth()->user()->can('Ver Clientes')
            ? Client::with('person')
                ->whereHas('person', function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('lastname', 'like', "%{$term}%")
                        ->orWhere('ci', 'like', "%{$term}%");
                })
                ->limit(5)
                ->get()
                ->map(fn ($client) => [
                    'id' => $client->id,
                    'label' => trim($client->person->name . ' ' . $client->person->lastname),
                ])
                ->toArray()
            : [];

        $this->products = auth()->user()->can('Ver Productos')
            ? Product::where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->limit(5)
                ->get(['id', 'name', 'code'])
                ->toArray()
            : [];
    }

    public function close()
    {
        $this->open = false;
    }

    public function render()
    {
        return view('livewire.global-search');
    }
}
