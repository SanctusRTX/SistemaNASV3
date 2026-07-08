<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificarSesion
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('loggedin')) {
            // Guardar la ruta a la que intentaba acceder
            if ($request->method() === 'GET') {
                session(['url.intended' => $request->fullUrl()]);
            }
            return redirect('/');
        }

        $response = $next($request);

        // Evitar que el navegador guarde en caché las páginas protegidas
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }
}