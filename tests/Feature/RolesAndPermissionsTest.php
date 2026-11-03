<?php

use App\Livewire\LivewireRoles;
use App\Livewire\LivewireUsers;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('a role can be created with a set of permissions', function () {
    Permission::firstOrCreate(['name' => 'Ver Roles', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'Ver Caja', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'Ver Ventas', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->givePermissionTo('Ver Roles');
    $this->actingAs($admin);

    Livewire::test(LivewireRoles::class)
        ->call('openModal')
        ->set('name', 'vendedor')
        ->set('selectedPermissions', ['Ver Caja', 'Ver Ventas'])
        ->call('save')
        ->assertHasNoErrors();

    $role = Role::where('name', 'vendedor')->first();

    expect($role)->not->toBeNull();
    expect($role->permissions->pluck('name')->sort()->values()->all())
        ->toBe(['Ver Caja', 'Ver Ventas']);
});

test('deleting a role also removes it from any user that had it assigned', function () {
    Permission::firstOrCreate(['name' => 'Ver Roles', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'Ver Caja', 'guard_name' => 'web']);

    $role = Role::create(['name' => 'temporal', 'guard_name' => 'web']);
    $role->givePermissionTo('Ver Caja');

    $worker = User::factory()->create();
    $worker->assignRole($role);

    $admin = User::factory()->create();
    $admin->givePermissionTo('Ver Roles');
    $this->actingAs($admin);

    Livewire::test(LivewireRoles::class)->call('delete', $role->id);

    expect(Role::find($role->id))->toBeNull();
    expect($worker->fresh()->roles)->toHaveCount(0);
});

test('a user inherits every permission from their assigned roles', function () {
    Permission::firstOrCreate(['name' => 'Ver Caja', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'Ver Ventas', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'Crear Ventas', 'guard_name' => 'web']);

    $role = Role::create(['name' => 'cajero', 'guard_name' => 'web']);
    $role->givePermissionTo(['Ver Caja', 'Ver Ventas', 'Crear Ventas']);

    $user = User::factory()->create();
    $user->assignRole($role);

    expect($user->can('Ver Caja'))->toBeTrue();
    expect($user->can('Crear Ventas'))->toBeTrue();
    expect($user->can('Ver Roles'))->toBeFalse();
});

test('editing a user through the Usuarios module can assign a role without touching direct permissions', function () {
    Permission::firstOrCreate(['name' => 'Ver Usuarios', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'Ver Caja', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'Ver Ventas', 'guard_name' => 'web']);

    $role = Role::create(['name' => 'cajero', 'guard_name' => 'web']);
    $role->givePermissionTo(['Ver Caja', 'Ver Ventas']);

    $admin = User::factory()->create();
    $admin->givePermissionTo('Ver Usuarios');
    $this->actingAs($admin);

    $target = User::factory()->create();

    Livewire::test(LivewireUsers::class)
        ->set('form.id', $target->id)
        ->set('form.updateMode', true)
        ->set('form.name', $target->name)
        ->set('form.email', $target->email)
        ->set('form.selectedRoles', ['cajero'])
        ->set('form.selectedPermissions', [])
        ->call('save')
        ->assertHasNoErrors();

    $target->refresh();

    expect($target->roles->pluck('name')->all())->toBe(['cajero']);
    expect($target->permissions)->toHaveCount(0);
    expect($target->getAllPermissions()->pluck('name')->sort()->values()->all())
        ->toBe(['Ver Caja', 'Ver Ventas']);
});

test('saving a user with no role and no direct permission is rejected', function () {
    Permission::firstOrCreate(['name' => 'Ver Usuarios', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->givePermissionTo('Ver Usuarios');
    $this->actingAs($admin);

    $target = User::factory()->create();

    Livewire::test(LivewireUsers::class)
        ->set('form.id', $target->id)
        ->set('form.updateMode', true)
        ->set('form.name', $target->name)
        ->set('form.email', $target->email)
        ->set('form.selectedRoles', [])
        ->set('form.selectedPermissions', [])
        ->call('save')
        ->assertHasErrors('form.selectedRoles');
});
