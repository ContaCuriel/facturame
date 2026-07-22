<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Si ni siquiera ha iniciado sesión, al login.
        if (!auth()->check()) {
            return redirect('/login');
        }

        $userRole = auth()->user()->role;

        // 2. Si es el dueño del sistema (SuperAdmin), tiene pase VIP a todo.
        if ($userRole === 'superadmin') {
            return $next($request);
        }

        // 3. Si el rol del usuario coincide con el rol exigido por la ruta, lo dejamos pasar.
        if ($userRole === $role) {
            return $next($request);
        }

        // 4. Si llegó hasta aquí, no tiene permiso. Le mostramos un error 403 (Acceso Denegado).
        abort(403, 'Acceso restringido. Tu nivel de usuario no permite ver esta sección.');
    }
}