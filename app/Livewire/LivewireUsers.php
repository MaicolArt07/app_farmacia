<?php

namespace App\Livewire;

use App\Livewire\Form\UsersForm;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class LivewireUsers extends Component
{
    use WithPagination;

    public $search = '';

    public $mode = 'list';

    public UsersForm $form;

    public $permissionGroups = [];

    public $personSearch = '';
    public $personResults = [];
    public $selectedPersonLabel = '';

    protected $paginationTheme = 'tailwind';

    public function mount($user = null)
    {
        if (request()->routeIs('users.create') || request()->routeIs('users.edit')) {
            $this->mode = 'form';

            $this->form->resetForm();

            $prefixes = ['Cambiar Estado', 'Ver', 'Crear', 'Editar', 'Exportar'];

            $this->permissionGroups = Permission::where('guard_name', 'web')
                ->orderBy('name')
                ->get()
                ->groupBy(function ($permission) use ($prefixes) {
                    $name = $permission->name;

                    foreach ($prefixes as $prefix) {
                        if (str_starts_with($name, $prefix . ' ')) {
                            return trim(substr($name, strlen($prefix)));
                        }
                    }

                    return $name;
                })
                ->sortKeys()
                ->toArray();

            if ($user) {
                $user = User::with(['permissions', 'person'])->findOrFail($user);
                $this->form->fillForm($user);

                if ($user->person) {
                    $this->selectedPersonLabel =
                        trim($user->person->nombre . ' ' . $user->person->apellido) . ' - CI: ' . $user->person->ci;
                }
            }
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        if ($this->mode === 'form') {
            return view('livewire.usuarios.form');
        }

        $query = User::with(['permissions', 'roles.permissions', 'person']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.usuarios.index', [
            'users' => $query->orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function updatedPersonSearch()
    {
        if (strlen(trim($this->personSearch)) < 2) {
            $this->personResults = [];
            return;
        }

        $this->personResults = Persona::query()
            ->where('estado', 1)
            ->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->personSearch}%")
                  ->orWhere('apellido', 'like', "%{$this->personSearch}%")
                  ->orWhere('ci', 'like', "%{$this->personSearch}%");
            })
            ->whereDoesntHave('user', function ($q) {
                if ($this->form->id) {
                    $q->where('id', '!=', $this->form->id);
                }
            })
            ->limit(8)
            ->get()
            ->map(function ($person) {
                return [
                    'id' => $person->id,
                    'label' => trim($person->nombre . ' ' . $person->apellido) . ' - CI: ' . $person->ci,
                ];
            })
            ->toArray();
    }

    public function selectPerson($id)
    {
        $person = Persona::findOrFail($id);

        $this->form->id_person = $person->id;
        $this->selectedPersonLabel = trim($person->nombre . ' ' . $person->apellido) . ' - CI: ' . $person->ci;
        $this->personSearch = '';
        $this->personResults = [];
    }

    public function clearPerson()
    {
        $this->form->id_person = null;
        $this->selectedPersonLabel = '';
        $this->personSearch = '';
        $this->personResults = [];
    }

    public function toggleGroup($group)
    {
        $names = collect($this->permissionGroups[$group] ?? [])->pluck('name')->all();

        if (empty($names)) {
            return;
        }

        $current = $this->form->selectedPermissions;
        $allSelected = empty(array_diff($names, $current));

        $this->form->selectedPermissions = $allSelected
            ? array_values(array_diff($current, $names))
            : array_values(array_unique(array_merge($current, $names)));
    }

    public function save()
    {
        $this->validate($this->form->rules(), $this->form->messages());

        if ($this->form->id) {
            $user = User::findOrFail($this->form->id);
            $user->id_person = $this->form->id_person;
            $user->name = $this->form->name;
            $user->email = $this->form->email;

            if ($this->form->password) {
                $user->password = Hash::make($this->form->password);
            }

            $user->save();

            $message = 'Usuario actualizado correctamente.';
        } else {
            $user = User::create([
                'id_person' => $this->form->id_person,
                'name' => $this->form->name,
                'email' => $this->form->email,
                'password' => Hash::make($this->form->password),
            ]);

            $message = 'Usuario creado correctamente.';
        }

        $user->syncPermissions($this->form->selectedPermissions);

        $this->dispatch('toast', type: 'success', message: $message);

        return redirect()->route('users');
    }

    public function cancel()
    {
        return redirect()->route('users');
    }

    public function delete($id)
    {
        try {
            User::findOrFail($id)->delete();
            $this->dispatch('toast', type: 'success', message: 'Usuario eliminado correctamente.');
        } catch (\Throwable $e) {
            $this->dispatch(
                'toast',
                type: 'error',
                message: 'No se puede eliminar: el usuario tiene registros asociados (ventas, compras, caja, etc.).'
            );
        }
    }
}
