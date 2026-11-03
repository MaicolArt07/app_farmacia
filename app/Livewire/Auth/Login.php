<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\SessionSecurityService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();
        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->email)->first();

        if (! $user || ! Auth::validate(['email' => $this->email, 'password' => $this->password])) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if (app(SessionSecurityService::class)->hasActiveSession($user)) {
            $this->dispatch('confirm-session-override');

            return;
        }

        $this->finalizeLogin($user);
    }

    /**
     * El usuario confirmó que quiere cerrar la sesión activa en el otro dispositivo.
     */
    #[On('confirmLogin')]
    public function confirmLogin(): void
    {
        $user = User::where('email', $this->email)->first();

        if (! $user || ! Auth::validate(['email' => $this->email, 'password' => $this->password])) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $this->finalizeLogin($user);
    }

    private function finalizeLogin(User $user): void
    {
        RateLimiter::clear($this->throttleKey());

        app(SessionSecurityService::class)->startSession($user, request());

        Auth::login($user, $this->remember);

        Session::regenerate();

        $this->redirectIntended(default: '/', navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
