<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectToFirstPermission
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }
    
        // Lista de permisos y sus rutas correspondientes
        $permissionsRoutes = [
            'Ver Inicio' => 'home',
            'Ver Usuarios' => 'users',
            'Ver Permisos' => 'permissions',
            'Ver Equipos' => 'equipment',
            'Ver Notas de Recepción' => 'reception-note',
            'Ver Personas' => 'person',
            'Ver Empleados' => 'employee',
            'Ver Empresas' => 'company',
            'Ver Contacto de Clientes' => 'contact-client',
            'Ver Cargos de Empleados' => 'employee-position',
        ];

        foreach ($permissionsRoutes as $permission => $route) {
            if ($user->can($permission)) {
                return redirect()->route($route);
            }
        }

        // Si no tiene permisos
        Auth::logout();
        return redirect()->route('login')->with('error', 'No tienes permisos asignados.');
    }
}
