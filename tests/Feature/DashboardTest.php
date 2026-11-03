<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;

test('guests are redirected to the login page', function () {
    $this->get('/')->assertRedirect('/login');
});

test('authenticated users without permissions are forbidden from home', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/')->assertForbidden();
});

test('authenticated users with "Ver Inicio" can visit the home page', function () {
    Permission::firstOrCreate(['name' => 'Ver Inicio', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->givePermissionTo('Ver Inicio');

    $this->actingAs($user);

    $this->get('/')->assertOk();
});
