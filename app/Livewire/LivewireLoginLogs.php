<?php

namespace App\Livewire;

use App\Models\LoginLog;
use Livewire\Component;
use Livewire\WithPagination;

class LivewireLoginLogs extends Component
{
    use WithPagination;

    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = LoginLog::with('user');

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            })->orWhere('ip_address', 'like', "%{$this->search}%");
        }

        return view('livewire.login-log.index', [
            'logs' => $query->orderBy('id', 'desc')->paginate(15),
        ]);
    }
}
