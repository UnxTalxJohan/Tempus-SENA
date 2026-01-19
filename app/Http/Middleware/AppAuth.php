<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AppAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $auth = $request->session()->get('app_auth');

        // Debe existir sesión y rol admin (id 1)
        if (!$auth || (int)($auth['rol_id'] ?? 0) !== 1) {
            // Mensaje genérico de acceso denegado
            return Redirect::route('login')->withErrors([
                'email' => 'Sin permiso de entrar. Solo usuarios con rol admin pueden acceder.'
            ]);
        }

        return $next($request);
    }
}
