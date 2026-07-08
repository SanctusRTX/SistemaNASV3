<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Exception;

class RemoteDashboardController extends Controller
{
    private function conectarFtp($server)
    {
        $password = '';
        try {
            $password = Crypt::decryptString($server->password);
        } catch (\Exception $e) {
            $password = '';
        }

        set_error_handler(function() {});
        $conn = ftp_connect($server->ip, (int)$server->puerto, 15);
        restore_error_handler();

        if (!$conn) return null;

        $usuario = ($server->usuario && strtolower($server->usuario) !== 'anonymous')
                    ? $server->usuario
                    : 'anonymous';

        $login = @ftp_login($conn, $usuario, $password);
        if (!$login) {
            $login = @ftp_login($conn, 'anonymous', '');
        }
        if (!$login) {
            ftp_close($conn);
            return null;
        }

        // Usar modo ACTIVO — PHP abre el canal de datos, compatible con PS3/WebMan
        // El modo pasivo falla porque la PS3 anuncia su IP interna como destino del canal de datos
        @ftp_pasv($conn, false);

        return $conn;
    }

    public function index(Request $request, $servidorId)
    {
        $server = DB::table('ss_servidores_externos')->where('id', $servidorId)->first();
        if (!$server) return redirect()->route('dashboard')->with('error', 'Servidor no encontrado');

        return view('dashboard', [
            'modulo'           => 'explorador_windows',
            'carpeta'          => $request->get('carpeta', ''),
            'servidor'         => $server,
            'termino'          => '',
            'carpetas'         => [],
            'todasLasCarpetas' => [],
            'datos'            => [],
            'usuarios'         => [],
            'servidores'       => [],
        ]);
    }

    public function obtenerDatos(Request $request, $servidorId)
    {
        $server = DB::table('ss_servidores_externos')->where('id', $servidorId)->first();
        if (!$server) {
            return response()->json(['carpetas' => [], 'archivos' => [], 'error' => 'Servidor no encontrado.']);
        }

        $carpeta = $request->get('carpeta', '');
        $rol     = session('rol');

        $conn = $this->conectarFtp($server);
        if (!$conn) {
            return response()->json([
                'carpetas' => [],
                'archivos' => [],
                'error'    => "No se pudo conectar a {$server->ip}:{$server->puerto}. Asegúrate de que el FTP esté activo."
            ]);
        }

        $carpetas = [];
        $archivos = [];

        try {
            // Determinar la ruta actual en el servidor
            $dirActual = $carpeta ?: '/';
            if (!str_starts_with($dirActual, '/')) $dirActual = '/' . $dirActual;

            $listaNList = @ftp_nlist($conn, $dirActual);

            if ($listaNList === false) {
                @ftp_close($conn);
                return response()->json([
                    'carpetas' => [],
                    'archivos' => [],
                    'error'    => 'No se pudo listar el directorio. Intenta recargar.'
                ]);
            }

            foreach ($listaNList as $ruta_item) {
                $nombre = basename($ruta_item);
                if ($nombre === '.' || $nombre === '..') continue;

                // Construir ruta absoluta para el servidor
                $rutaAbsoluta = rtrim($dirActual, '/') . '/' . $nombre;
                // Ruta relativa para el sistema (sin / inicial)
                $ruta = ltrim($rutaAbsoluta, '/');

                // Detectar si es carpeta intentando hacer chdir
                $esCarpeta = @ftp_chdir($conn, $rutaAbsoluta);
                if ($esCarpeta) {
                    // Volver al directorio anterior
                    @ftp_chdir($conn, $dirActual);
                }

                if ($esCarpeta) {
                    $carpetas[] = [
                        'nombre'         => $nombre,
                        'ruta'           => $ruta,
                        'size'           => '--',
                        'fecha'          => '--',
                        'items'          => '--',
                        'puedeRenombrar' => $rol !== 'secretario',
                        'puedeEliminar'  => $rol !== 'secretario',
                    ];
                } else {
                    $fileSize = @ftp_size($conn, $rutaAbsoluta);
                    $sizeStr  = $fileSize > 0 ? round($fileSize / 1024, 2) . ' KB' : '--';
                    $ext      = pathinfo($nombre, PATHINFO_EXTENSION);
                    $archivos[] = [
                        'nombre'         => $nombre,
                        'ruta'           => $ruta,
                        'size'           => $sizeStr,
                        'fecha'          => '--',
                        'icono'          => $this->getIcono($ext),
                        'esTexto'        => false,
                        'puedeEditar'    => false,
                        'puedeRenombrar' => $rol !== 'secretario',
                        'puedeEliminar'  => $rol !== 'secretario',
                    ];
                }
            }

            @ftp_close($conn);

        } catch (Exception $e) {
            @ftp_close($conn);
            return response()->json([
                'carpetas' => [],
                'archivos' => [],
                'error'    => 'Error al leer archivos: ' . $e->getMessage()
            ]);
        }

        return response()->json([
            'carpetas' => $carpetas,
            'archivos' => $archivos,
        ]);
    }

    private function getIcono($ext)
    {
        $iconos = [
            'pdf'  => 'fa-file-pdf text-danger',
            'doc'  => 'fa-file-word text-primary',
            'docx' => 'fa-file-word text-primary',
            'xls'  => 'fa-file-excel text-success',
            'xlsx' => 'fa-file-excel text-success',
            'jpg'  => 'fa-file-image text-info',
            'jpeg' => 'fa-file-image text-info',
            'png'  => 'fa-file-image text-info',
            'zip'  => 'fa-file-archive text-warning',
            'rar'  => 'fa-file-archive text-warning',
            'txt'  => 'fa-file-alt text-secondary',
            'iso'  => 'fa-compact-disc text-warning',
            'pkg'  => 'fa-box text-info',
            'ps3'  => 'fa-gamepad text-danger',
        ];
        return $iconos[strtolower($ext)] ?? 'fa-file text-secondary';
    }

    // ==================== FUNCIONES REMOTAS ====================

    public function renombrar(Request $request, $servidorId)
    {
        $server = DB::table('ss_servidores_externos')->where('id', $servidorId)->first();
        if (!$server) return response()->json(['success' => false, 'message' => 'Servidor no encontrado.']);

        $rol = session('rol');
        if ($rol === 'secretario') return response()->json(['success' => false, 'message' => 'No autorizado.']);

        $ruta = ltrim($request->input('ruta', ''), '/');
        $nuevoNombre = trim($request->input('nuevo_nombre', ''));
        
        if (empty($ruta) || empty($nuevoNombre)) {
            return response()->json(['success' => false, 'message' => 'Faltan parámetros.']);
        }

        $conn = $this->conectarFtp($server);
        if (!$conn) return response()->json(['success' => false, 'message' => 'Error de conexión FTP.']);

        $dirPadre = dirname($ruta);
        if ($dirPadre === '.' || $dirPadre === '\\') $dirPadre = '';
        
        $nuevaRuta = ($dirPadre ? $dirPadre . '/' : '') . $nuevoNombre;
        
        // Mantener extensión si es archivo
        if ($request->input('tipo_elemento') === 'archivo') {
            $ext = pathinfo($ruta, PATHINFO_EXTENSION);
            if ($ext && strtolower(pathinfo($nuevoNombre, PATHINFO_EXTENSION)) !== strtolower($ext)) {
                $nuevaRuta .= '.' . $ext;
            }
        }

        $resultado = @ftp_rename($conn, '/' . $ruta, '/' . $nuevaRuta);
        @ftp_close($conn);

        if ($resultado) {
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'No se pudo renombrar el elemento.']);
    }

    public function eliminar(Request $request, $servidorId)
    {
        $server = DB::table('ss_servidores_externos')->where('id', $servidorId)->first();
        if (!$server) return response()->json(['success' => false, 'message' => 'Servidor no encontrado.']);

        $rol = session('rol');
        if ($rol !== 'administrador') return response()->json(['success' => false, 'message' => 'No autorizado.']);

        $ruta = ltrim($request->input('ruta', ''), '/');
        if (empty($ruta)) return response()->json(['success' => false, 'message' => 'Ruta no válida.']);

        $conn = $this->conectarFtp($server);
        if (!$conn) return response()->json(['success' => false, 'message' => 'Error de conexión FTP.']);

        $tipo = $request->input('tipo', 'archivo');
        $rutaAbsoluta = '/' . $ruta;

        $resultado = false;
        if ($tipo === 'carpeta') {
            // Eliminar carpeta (debe estar vacía en FTP básico)
            $resultado = @ftp_rmdir($conn, $rutaAbsoluta);
            if (!$resultado) {
                // Si falla, quizás no está vacía o es un archivo reportado como carpeta
                $resultado = @ftp_delete($conn, $rutaAbsoluta);
            }
        } else {
            $resultado = @ftp_delete($conn, $rutaAbsoluta);
        }

        @ftp_close($conn);

        if ($resultado) return response()->json(['success' => true]);
        return response()->json(['success' => false, 'message' => 'No se pudo eliminar (la carpeta debe estar vacía).']);
    }

    public function crearCarpeta(Request $request, $servidorId)
    {
        $server = DB::table('ss_servidores_externos')->where('id', $servidorId)->first();
        if (!$server) return response()->json(['success' => false, 'message' => 'Servidor no encontrado.']);

        $rol = session('rol');
        if ($rol === 'secretario') return response()->json(['success' => false, 'message' => 'No autorizado.']);

        $nombre = trim($request->input('nombre', ''));
        $carpetaPadre = trim($request->input('carpeta_padre', ''), '/');

        if (empty($nombre)) return response()->json(['success' => false, 'message' => 'Nombre inválido.']);

        $conn = $this->conectarFtp($server);
        if (!$conn) return response()->json(['success' => false, 'message' => 'Error de conexión FTP.']);

        $rutaAbsoluta = '/' . ($carpetaPadre ? $carpetaPadre . '/' : '') . $nombre;
        
        $resultado = @ftp_mkdir($conn, $rutaAbsoluta);
        @ftp_close($conn);

        if ($resultado) return response()->json(['success' => true, 'message' => 'Carpeta creada exitosamente.']);
        return response()->json(['success' => false, 'message' => 'No se pudo crear la carpeta.']);
    }

    public function descargar(Request $request, $servidorId)
    {
        $server = DB::table('ss_servidores_externos')->where('id', $servidorId)->first();
        if (!$server) abort(404, 'Servidor no encontrado');

        $ruta = ltrim($request->get('archivo', ''), '/');
        if (empty($ruta)) abort(400, 'Archivo no válido');

        $conn = $this->conectarFtp($server);
        if (!$conn) abort(500, 'Error conectando al servidor FTP');

        $tempPath = storage_path('app/temp/ftp_' . uniqid() . '_' . basename($ruta));
        $dir = dirname($tempPath);
        if (!file_exists($dir)) mkdir($dir, 0755, true);

        // Descargar el archivo al servidor local temporalmente
        $resultado = @ftp_get($conn, $tempPath, '/' . $ruta, FTP_BINARY);
        @ftp_close($conn);

        if (!$resultado) {
            if (file_exists($tempPath)) unlink($tempPath);
            abort(500, 'Error descargando archivo desde el FTP remoto.');
        }

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    public function subir(Request $request, $servidorId)
    {
        $server = DB::table('ss_servidores_externos')->where('id', $servidorId)->first();
        if (!$server) return response()->json(['success' => false, 'message' => 'Servidor no encontrado.']);

        $rol = session('rol');
        if ($rol === 'secretario') {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para subir archivos.']);
        }

        if (!$request->hasFile('archivo')) {
            return response()->json(['success' => false, 'message' => 'No se recibió ningún archivo.']);
        }

        $archivo = $request->file('archivo');
        $carpetaDestino = ltrim($request->input('carpeta_destino', ''), '/');

        // Guardar el archivo temporalmente en el NAS
        $tempPath = storage_path('app/temp/upload_' . uniqid() . '_' . $archivo->getClientOriginalName());
        $tempDir  = dirname($tempPath);
        if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);

        $archivo->move($tempDir, basename($tempPath));

        if (!file_exists($tempPath)) {
            return response()->json(['success' => false, 'message' => 'Error al guardar el archivo temporalmente.']);
        }

        // Conectar al FTP remoto
        $conn = $this->conectarFtp($server);
        if (!$conn) {
            unlink($tempPath);
            return response()->json(['success' => false, 'message' => 'No se pudo conectar al servidor FTP.']);
        }

        // Ruta de destino en el servidor remoto
        $nombre       = $archivo->getClientOriginalName();
        $rutaRemota   = '/' . ($carpetaDestino ? $carpetaDestino . '/' : '') . $nombre;

        // Subir al servidor FTP en modo binario
        $resultado = @ftp_put($conn, $rutaRemota, $tempPath, FTP_BINARY);
        @ftp_close($conn);

        // Limpiar archivo temporal
        if (file_exists($tempPath)) unlink($tempPath);

        if ($resultado) {
            return response()->json(['success' => true, 'message' => "'{$nombre}' subido correctamente."]);
        }

        return response()->json(['success' => false, 'message' => 'No se pudo subir el archivo. El servidor puede no tener permisos de escritura.']);
    }
}
