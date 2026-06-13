<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BlockStudentFromAdminRoutes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->persona?->tipo_Postulante) {
            $routeName = $request->route()?->getName();
            
            $allowedRoutes = [
                'estudiante.perfil',
                'estudiante.estado-inscripcion',
                'estudiante.estado-admision',
                'estudiante.notas',
                'estudiante.pagar-matricula',
                'estudiante.boleta-inscripcion',
                'logout',
                'pagos.paypal.pagar',
                'paypal.success',
                'paypal.cancel',
                'emitirComprobante',
            ];

            if (in_array($routeName, $allowedRoutes) || $request->is('estudiante/*', 'paypal/*', 'logout')) {
                return $next($request);
            }

            return redirect()->route('estudiante.perfil')->withErrors([
                'error' => 'No tienes permiso para acceder a la sección administrativa.'
            ]);
        }

        return $next($request);
    }
}
