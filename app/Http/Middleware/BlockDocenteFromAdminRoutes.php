<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BlockDocenteFromAdminRoutes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->persona?->tipo_Docente) {
            $routeName = $request->route()?->getName();
            
            $allowedRoutes = [
                'docente.perfil',
                'docente.materias',
                'docente.grupos',
                'docente.grupos.estudiantes',
                'docente.notas',
                'logout',
            ];

            if (in_array($routeName, $allowedRoutes) || $request->is('docente/*', 'logout')) {
                return $next($request);
            }

            return redirect()->route('docente.perfil')->withErrors([
                'error' => 'No tienes permiso para acceder a la sección administrativa.'
            ]);
        }

        return $next($request);
    }
}
