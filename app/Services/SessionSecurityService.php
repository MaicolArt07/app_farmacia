<?php

namespace App\Services;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class SessionSecurityService
{
    /**
     * Si el usuario ya tiene un token de sesión activo, significa que hay
     * una sesión abierta (en este u otro dispositivo) que se cerrará al
     * iniciar una nueva.
     */
    public function hasActiveSession(User $user): bool
    {
        return filled($user->session_token);
    }

    /**
     * Genera un nuevo token de sesión, lo guarda en users.session_token y en
     * la sesión actual, y registra el login. Este es el ÚNICO lugar donde se
     * actualiza users.session_token al iniciar sesión.
     */
    public function startSession(User $user, Request $request): string
    {
        $token = (string) Str::uuid();

        $user->forceFill(['session_token' => $token])->save();

        session(['session_token' => $token]);

        $this->log($user, $request, 'login', $token);

        return $token;
    }

    /**
     * Cierre de sesión explícito (botón "Cerrar sesión"): libera el token
     * para que el próximo login no muestre la alerta de "vas a cerrar otra sesión".
     */
    public function recordLogout(User $user, Request $request): void
    {
        $this->log($user, $request, 'logout', $user->session_token);

        $user->forceFill(['session_token' => null])->save();
    }

    /**
     * Cierre forzado por el middleware cuando otro dispositivo inició sesión
     * con la misma cuenta. NO toca users.session_token: ese valor ya pertenece
     * a la sesión nueva que reemplazó a esta.
     */
    public function recordForcedLogout(User $user, Request $request, ?string $token): void
    {
        $this->log($user, $request, 'forced_logout', $token);
    }

    protected function log(User $user, Request $request, string $action, ?string $token): void
    {
        $agent = new Agent();
        $agent->setUserAgent((string) $request->userAgent());

        LoginLog::create([
            'id_usuario' => $user->id,
            'session_token' => $token,
            'action' => $action,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent->browser() ?: null,
            'platform' => $agent->platform() ?: null,
            'device' => $agent->isMobile() ? 'Móvil' : ($agent->isTablet() ? 'Tablet' : 'Escritorio'),
            'created_at' => now(),
        ]);
    }
}
