<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerificarRol
{
    /**
     * Maneja la solicitud entrante verificando que el rol del usuario
     * esté entre los roles permitidos para esa ruta.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$rolesPermitidos  Roles que pueden acceder (ej: 'administrador', 'supervisor')
     */
    public function handle(Request $request, Closure $next, string ...$rolesPermitidos)
    {
        // Si no está autenticado, redirigir al login
        if (!session('loggedin')) {
            return redirect('/');
        }

        $rolUsuario = session('rol', 'secretario');

        // Si el rol del usuario está en la lista de permitidos, continuar
        if (in_array($rolUsuario, $rolesPermitidos)) {
            $response = $next($request);

            // Evitar caché en páginas protegidas
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

            return $response;
        }

        // Registrar intento de acceso no autorizado
        Log::warning('ACCESO_DENEGADO: Intento de acceso sin permisos', [
            'usuario'   => session('username', 'desconocido'),
            'rol'       => $rolUsuario,
            'ruta'      => $request->path(),
            'ip'        => $request->ip(),
            'timestamp' => now()->toISOString(),
        ]);

        // Si es una petición AJAX / JSON, devolver 403 en JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para realizar esta acción.',
                'rol'     => $rolUsuario,
            ], 403);
        }

        // Si es una petición normal, redirigir a la página de acceso denegado
        return redirect()->route('acceso.denegado')
            ->with('modulo_bloqueado', $request->path())
            ->with('rol_actual', $rolUsuario);
    }
}
