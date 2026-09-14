<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
            $googleUser = Socialite::driver('google')->stateless()->user(); // usa stateless en local

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Crear nuevo usuario
                $user = User::create([
                    'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Sin nombre',
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(uniqid()), // puedes guardar también el avatar si gustas
                ]);
            }

            Auth::login($user);

            return redirect('/'); // o la ruta que uses para tu dashboard

    }
}
