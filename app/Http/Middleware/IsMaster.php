<?php
// Archivo: app/Http/Middleware/IsMaster.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsMaster
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificamos si el usuario está logueado y si su rol es el de dueño del SaaS
        if (Auth::check() && Auth::user()->rol === 'master_root') {
            return $next($request);
        }

        // Si un alcalde curioso intenta entrar a mimunicipio.com/master, el sistema lo bota.
        abort(403, 'ACCESO DENEGADO: Área restringida al corporativo de MiMunicipio.');
    }
}