<?php

use App\Livewire\Auth\Login;
use App\Models\LoginLog;
use App\Models\User;
use Livewire\Livewire;

test('a fresh login sets a session token and logs it', function () {
    $user = User::factory()->create(['password' => bcrypt('secret123')]);

    expect($user->session_token)->toBeNull();

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'secret123')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('home', absolute: false));

    $user->refresh();

    expect($user->session_token)->not->toBeNull();
    expect(LoginLog::where('id_usuario', $user->id)->where('action', 'login')->count())->toBe(1);
});

test('logging in again while a stale session token exists logs in directly with a brand new token', function () {
    $user = User::factory()->create(['password' => bcrypt('secret123')]);
    $user->forceFill(['session_token' => 'old-device-token'])->save();

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'secret123')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('home', absolute: false));

    $this->assertAuthenticatedAs($user);

    expect($user->fresh()->session_token)
        ->not->toBeNull()
        ->not->toBe('old-device-token');
});

test('a mismatched session token forces logout on the next request', function () {
    $user = User::factory()->create();
    $user->forceFill(['session_token' => 'token-from-another-device'])->save();

    // La sesión actual nunca se "sincronizó" con ese token (simula haber sido
    // reemplazada por otro dispositivo después de iniciar sesión aquí).
    $this->actingAs($user);
    session(['session_token' => 'this-browser-old-token']);

    $this->get('/')
        ->assertRedirect(route('login', absolute: false));

    $this->assertGuest();

    expect(LoginLog::where('id_usuario', $user->id)->where('action', 'forced_logout')->count())->toBe(1);
});

test('logging out manually clears the session token', function () {
    $user = User::factory()->create();
    $token = 'token-to-release';
    $user->forceFill(['session_token' => $token])->save();

    $this->actingAs($user);
    session(['session_token' => $token]);

    $this->post('/logout')->assertRedirect('/');

    expect($user->fresh()->session_token)->toBeNull();
    expect(LoginLog::where('id_usuario', $user->id)->where('action', 'logout')->count())->toBe(1);
});
