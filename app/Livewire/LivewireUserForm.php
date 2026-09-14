<?php

namespace App\Livewire;

use App\Livewire\Form\UsersForm;
use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class LivewireUserForm extends Component
{
    public UsersForm $form;

    public $permissionGroups = [];

    public $personSearch = '';
    public $personResults = [];
    public $selectedPersonLabel = '';

    public function mount($user = null)
    {
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
                    trim($user->person->name . ' ' . $user->person->lastname) . ' - CI: ' . $user->person->ci;
            }
        }
    }

    public function updatedPersonSearch()
    {
        if (strlen(trim($this->personSearch)) < 2) {
            $this->personResults = [];
            return;
        }

        $this->personResults = Person::query()
            ->where('state', 1)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->personSearch}%")
                  ->orWhere('lastname', 'like', "%{$this->personSearch}%")
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
                    'label' => trim($person->name . ' ' . $person->lastname) . ' - CI: ' . $person->ci,
                ];
            })
            ->toArray();
    }

    public function selectPerson($id)
    {
        $person = Person::findOrFail($id);

        $this->form->id_person = $person->id;
        $this->selectedPersonLabel = trim($person->name . ' ' . $person->lastname) . ' - CI: ' . $person->ci;
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

    public function render()
    {
        return view('livewire.users.form');
    }
}
