<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $basePath;

    public function __construct()
    {
        $this->basePath = storage_path('app/Almacenamiento');
    }

    public function index(Request $request)
    {
        if (!session('loggedin')) {
            return redirect('/');
        }

        if (!file_exists($this->basePath)) {
            mkdir($this->basePath, 0777, true);
        }

        $carpetas = $this->listarCarpetas($this->basePath);
        $carpeta  = $request->get('carpeta', '');
        $termino  = $request->get('buscar', '');
        $modulo   = $request->get('modulo', 'explorador_windows');

        // Si hay búsqueda, forzar módulo buscar
        if (!empty($termino)) {
            $modulo = 'buscar';
        }

        // ==================== CONTROL DE ACCESO POR MÓDULO ====================
        $rol = session('rol', 'secretario');

        // Módulos restringidos solo para administrador
        $modulosAdminOnly = ['papelera', 'usuarios'];

        // Módulos para administrador y supervisor
        $modulosAdminSupervisor = ['crear_carpeta', 'copiarmover'];

        if (in_array($modulo, $modulosAdminOnly) && $rol !== 'administrador') {
            return redirect()->route('acceso.denegado')
                ->with('modulo_bloqueado', $modulo)
                ->with('rol_actual', $rol);
        }

        if (in_array($modulo, $modulosAdminSupervisor) && !in_array($rol, ['administrador', 'supervisor'])) {
            return redirect()->route('acceso.denegado')
                ->with('modulo_bloqueado', $modulo)
                ->with('rol_actual', $rol);
        }
        // ======================================================================

        // Datos adicionales según módulo
        $todasLasCarpetas = [];
        $datos            = [];
        $usuarios         = [];
        $computadoras     = collect();

        if (in_array($modulo, ['crear_carpeta', 'crear_archivo', 'copiarmover'])) {
            $todasLasCarpetas = $this->listarTodasLasCarpetas($this->basePath, $this->basePath);
        }

        if ($modulo === 'usuarios') {
            // Se obtienen los usuarios excluyendo contraseñas (solo ID, usuario, rol)
            $usuarios = \Illuminate\Support\Facades\DB::table('ss_usuarios')->get();
        }

        if ($modulo === 'computadoras') {
            $computadoras = \App\Models\Computadora::orderBy('departamento')->orderBy('nombre')->get();
        }

        $servidores = [];
        if ($modulo === 'servidores') {
            $servidores = \Illuminate\Support\Facades\DB::table('ss_servidores_externos')->get();
        }

        if ($modulo === 'editar_archivo') {
            $rutaRelativa = str_replace('..', '', $request->get('archivo', ''));
            $rutaAbsoluta = $this->basePath . DIRECTORY_SEPARATOR
                . str_replace('/', DIRECTORY_SEPARATOR, ltrim($rutaRelativa, '/'));

            if (file_exists($rutaAbsoluta) && is_file($rutaAbsoluta)) {
                $datos = [
                    'contenido' => file_get_contents($rutaAbsoluta),
                    'nombre'    => basename($rutaAbsoluta),
                    'ruta'      => $rutaRelativa,
                ];
            }
        }

        return view('dashboard', compact(
            'carpetas', 'modulo', 'carpeta',
            'todasLasCarpetas', 'datos', 'termino', 'usuarios', 'servidores', 'computadoras'
        ));
    }

    private function listarCarpetas($ruta)
    {
        $carpetas = [];
        if (is_dir($ruta)) {
            foreach (scandir($ruta) as $item) {
                if ($item !== '.' && $item !== '..' && is_dir($ruta . '/' . $item)) {
                    $carpetas[] = $item;
                }
            }
        }
        return $carpetas;
    }

    private function listarTodasLasCarpetas($path, $basePath, $level = 0)
    {
        $carpetas = [];
        if (!is_dir($path)) return $carpetas;

        foreach (scandir($path) as $item) {
            if ($item === '.' || $item === '..') continue;
            $fullPath = $path . '/' . $item;
            if (is_dir($fullPath)) {
                $relativa   = ltrim(str_replace($basePath, '', $fullPath), '/\\');
                $carpetas[] = ['name' => $relativa, 'level' => $level];
                $carpetas   = array_merge(
                    $carpetas,
                    $this->listarTodasLasCarpetas($fullPath, $basePath, $level + 1)
                );
            }
        }
        return $carpetas;
    }
}