<?php

namespace App\Http\Middleware;

use App\Services\SessionSecurityService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class ForceSingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            $currentToken = session('session_token');
            $dbToken = $user->session_token;

            if ($dbToken && $currentToken && $dbToken !== $currentToken) {
                app(SessionSecurityService::class)->recordForcedLogout($user, $request, $currentToken);

                Auth::guard('web')->logout();
                Session::invalidate();
                Session::regenerateToken();

                return redirect()->route('login')
                    ->with('status', 'Tu sesión se cerró porque se inició sesión con esta cuenta desde otro dispositivo.');
            }
        }

        return $next($request);
    }
}
