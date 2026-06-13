<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * OnlyDocente
 *
 * Restringe el acceso a las rutas del portal del docente.
 * Solo pasan usuarios autenticados cuya persona tenga tipo_Docente = true.
 */
class OnlyDocente
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->persona?->tipo_Docente) {
            return $next($request);
        }

        // Si es un postulante, redirigir a su portal
        if (Auth::check() && Auth::user()->persona?->tipo_Postulante) {
            return redirect()->route('estudiante.perfil')->withErrors([
                'error' => 'Acceso denegado. Redirigido al portal del estudiante.'
            ]);
        }

        // Si es admin u otro rol, redirigir al menú general
        return redirect()->route('menu')->withErrors([
            'error' => 'No tienes permisos de docente para acceder a esta área.'
        ]);
    }
}
