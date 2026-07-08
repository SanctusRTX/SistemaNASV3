<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class FileController extends Controller
{
    private $basePath;

    public function __construct()
    {
        $this->basePath = storage_path('app/Almacenamiento');
        if (!file_exists($this->basePath)) {
            mkdir($this->basePath, 0755, true);
        }
    }

    /**
     * Valida y sanea una ruta para prevenir Path Traversal
     */
    private function validarRuta($rutaRelativa)
    {
        // Eliminar caracteres peligrosos y normalizar
        $rutaRelativa = str_replace(['..', '\\'], ['', '/'], $rutaRelativa);
        $rutaRelativa = preg_replace('/\/+/', '/', $rutaRelativa);
        $rutaRelativa = trim($rutaRelativa, '/');
        
        // Construir ruta absoluta
        $rutaAbsoluta = $this->basePath;
        if (!empty($rutaRelativa)) {
            $rutaAbsoluta .= '/' . $rutaRelativa;
        }
        
        // Verificar que la ruta esté dentro del basePath
        $rutaReal = realpath($rutaAbsoluta);
        $basePathReal = realpath($this->basePath);
        
        if ($rutaReal === false || strpos($rutaReal, $basePathReal) !== 0) {
            return false;
        }
        
        return [
            'absoluta' => $rutaAbsoluta,
            'relativa' => $rutaRelativa,
            'real' => $rutaReal
        ];
    }

    /**
     * Valida tipos de archivo: solo bloquea extensiones peligrosas.
     * Cualquier otro archivo (iso, db, bin, etc.) queda permitido.
     */
    private function validarTipoArchivo($extension)
    {
        $extension = strtolower(ltrim($extension, '.'));

        // Únicamente estas extensiones están bloqueadas por seguridad del servidor
        $extensionesProhibidas = [
            'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'phar',
            'exe', 'bat', 'cmd', 'com', 'scr', 'vbs',
            'sh', 'ps1', 'ps2', 'bash', 'zsh',
            'asp', 'aspx', 'jsp', 'jspx',
            'htaccess', 'htpasswd',
        ];

        // Bloquear si está en la lista negra; todo lo demás se permite
        return !in_array($extension, $extensionesProhibidas);
    }

    /**
     * Verifica el tamaño de archivo
     */
    private function validarTamañoArchivo($tamaño)
    {
        $maxSize = 50 * 1024 * 1024; // 50MB
        return $tamaño <= $maxSize;
    }

    /**
     * Registra una acción crítica para auditoría
     */
    private function logAccion($accion, $detalles = [])
    {
        $usuario = session('username', 'desconocido');
        $ip = request()->ip();
        
        Log::info("ACCION_CRITICA: $accion", [
            'usuario' => $usuario,
            'ip' => $ip,
            'accion' => $accion,
            'detalles' => $detalles,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function explorador(Request $request)
    {
        if (!session('loggedin')) {
            return response('No autorizado', 401);
        }

        $carpetaActual = $request->get('carpeta', '');
        $rutaValida = $this->validarRuta($carpetaActual);
        
        if (!$rutaValida) {
            return response('<div class="alert alert-danger">Ruta no válida.</div>', 403);
        }
        
        $rutaActual = $rutaValida['absoluta'];
        $carpetaActual = $rutaValida['relativa'];

        if (!is_dir($rutaActual)) {
            return response('<div class="alert alert-danger">La carpeta solicitada no existe.</div>');
        }

        $carpetas = array_filter(scandir($rutaActual), function($item) use ($rutaActual) {
            return $item != '.' && $item != '..' && is_dir($rutaActual . '/' . $item);
        });

        $archivos = array_filter(scandir($rutaActual), function($item) use ($rutaActual) {
            return $item != '.' && $item != '..' && !is_dir($rutaActual . '/' . $item);
        });

        sort($carpetas);
        sort($archivos);

        $rol = session('rol', 'secretario');

        return response()->json([
            'carpetas' => $this->procesarCarpetas($carpetas, $rutaActual, $carpetaActual, $rol),
            'archivos' => $this->procesarArchivos($archivos, $rutaActual, $carpetaActual, $rol),
        ]);
    }

    private function procesarCarpetas($carpetas, $rutaActual, $rutaRelativa, $rol)
    {
        $resultado = [];
        foreach ($carpetas as $carpeta) {
            $rutaCarpeta = ltrim($rutaRelativa . '/' . $carpeta, '/');
            $carpetaPath = $rutaActual . '/' . $carpeta;
            $info = $this->getFolderSize($carpetaPath);

            $resultado[] = [
                'nombre' => $carpeta,
                'ruta' => $rutaCarpeta,
                'size' => $this->formatSize($info['size']),
                'items' => $info['files'] + $info['dirs'],
                'fecha' => date('d/m/Y H:i', filemtime($carpetaPath)),
                'puedeEliminar' => $this->tienePermiso($rol, 'eliminar'),
                'puedeRenombrar' => $this->tienePermiso($rol, 'renombrar'),
            ];
        }
        return $resultado;
    }

    private function procesarArchivos($archivos, $rutaActual, $rutaRelativa, $rol)
    {
        $resultado = [];
        $extensionesTexto = ['txt', 'html', 'htm', 'css', 'js', 'php', 'json', 'xml', 'md', 'csv', 'log', 'rtf', 'odt', 'tex'];

        foreach ($archivos as $archivo) {
            $rutaArchivo = ltrim($rutaRelativa . '/' . $archivo, '/');
            $archivoPath = $rutaActual . '/' . $archivo;
            $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            $preview = $this->clasificarPreview($extension);

            $resultado[] = [
                'nombre' => $archivo,
                'ruta' => $rutaArchivo,
                'size' => $this->formatSize(filesize($archivoPath)),
                'fecha' => date('d/m/Y H:i', filemtime($archivoPath)),
                'icono' => $this->getIcono($extension),
                'esTexto' => in_array($extension, $extensionesTexto),
                'tipoPreview' => $preview['tipo'],
                'puedePrevisualizar' => $preview['puede'],
                'puedeEliminar' => $this->tienePermiso($rol, 'eliminar'),
                'puedeRenombrar' => $this->tienePermiso($rol, 'renombrar'),
                'puedeEditar' => $this->tienePermiso($rol, 'crear_archivo'),
            ];
        }
        return $resultado;
    }

    private function tienePermiso($rol, $accion)
    {
        $permisos = [
            // Administrador: acceso total a todo
            'administrador' => [
                'subir'           => true,
                'descargar'       => true,
                'crear_archivo'   => true,
                'crear_carpeta'   => true,
                'eliminar'        => true,
                'renombrar'       => true,
                'mover'           => true,
                'copiar'          => true,
                'papelera'        => true,
                'restaurar'       => true,
                'vaciar_papelera' => true,
            ],
            // Supervisor: igual que administrador EXCEPTO papelera y eliminar
            'supervisor' => [
                'subir'           => true,
                'descargar'       => true,
                'crear_archivo'   => true,
                'crear_carpeta'   => true,
                'eliminar'        => false,   // No puede eliminar
                'renombrar'       => true,
                'mover'           => true,
                'copiar'          => true,
                'papelera'        => false,   // Sin acceso a la papelera
                'restaurar'       => false,
                'vaciar_papelera' => false,
            ],
            // Secretario: solo subir archivos, crear documentos y renombrar carpetas
            'secretario' => [
                'subir'           => true,
                'descargar'       => true,
                'crear_archivo'   => true,   // Crear documentos
                'crear_carpeta'   => false,
                'eliminar'        => false,
                'renombrar'       => true,   // Puede renombrar carpetas
                'mover'           => false,
                'copiar'          => false,
                'papelera'        => false,
                'restaurar'       => false,
                'vaciar_papelera' => false,
            ],
        ];
        return $permisos[$rol][$accion] ?? false;
    }

    private function getFolderSize($path)
    {
        $total_size = 0;
        $file_count = 0;
        $dir_count = 0;
        
        if (!is_dir($path)) {
            return ['size' => 0, 'files' => 0, 'dirs' => 0];
        }
        
        // Usar iterador para evitar recursión profunda
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    $dir_count++;
                } else {
                    $total_size += $file->getSize();
                    $file_count++;
                }
            }
        } catch (\Exception $e) {
            // En caso de error, retornar valores seguros
            return ['size' => 0, 'files' => 0, 'dirs' => 0];
        }
        
        return ['size' => $total_size, 'files' => $file_count, 'dirs' => $dir_count];
    }

    private function formatSize($bytes)
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' bytes';
    }

    private function getIcono($extension)
    {
        $iconos = [
            'txt' => 'fa-file-alt', 'html' => 'fa-file-alt', 'htm' => 'fa-file-alt',
            'css' => 'fa-file-alt', 'js' => 'fa-file-alt', 'php' => 'fa-file-alt',
            'json' => 'fa-file-alt', 'xml' => 'fa-file-alt', 'md' => 'fa-file-alt',
            'jpg' => 'fa-file-image', 'jpeg' => 'fa-file-image', 'png' => 'fa-file-image',
            'gif' => 'fa-file-image', 'bmp' => 'fa-file-image', 'svg' => 'fa-file-image',
            'pdf' => 'fa-file-pdf', 'doc' => 'fa-file-word', 'docx' => 'fa-file-word',
            'xls' => 'fa-file-excel', 'xlsx' => 'fa-file-excel',
            'ppt' => 'fa-file-powerpoint', 'pptx' => 'fa-file-powerpoint',
            'zip' => 'fa-file-archive', 'rar' => 'fa-file-archive', '7z' => 'fa-file-archive',
            'mp3' => 'fa-file-audio', 'wav' => 'fa-file-audio', 'ogg' => 'fa-file-audio',
            'mp4' => 'fa-file-video', 'avi' => 'fa-file-video', 'mov' => 'fa-file-video',
        ];
        return $iconos[$extension] ?? 'fa-file';
    }

    private function clasificarPreview(string $extension): array
    {
        $extension = strtolower(ltrim($extension, '.'));

        if (!$this->validarTipoArchivo($extension)) {
            return ['tipo' => 'none', 'puede' => false];
        }

        $mapa = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'],
            'pdf'   => ['pdf'],
            'video' => ['mp4', 'webm', 'mov', 'avi', 'mkv'],
            'audio' => ['mp3', 'wav', 'ogg', 'flac'],
            'text'  => ['txt', 'md', 'json', 'xml', 'csv', 'log', 'css', 'js', 'html', 'htm', 'rtf', 'tex', 'yaml', 'yml', 'ini', 'conf', 'sql', 'env'],
        ];

        foreach ($mapa as $tipo => $extensiones) {
            if (in_array($extension, $extensiones, true)) {
                return ['tipo' => $tipo, 'puede' => true];
            }
        }

        return ['tipo' => 'none', 'puede' => false];
    }

    private function obtenerMimePreview(string $rutaAbsoluta, string $extension): string
    {
        $mime = @mime_content_type($rutaAbsoluta);
        if ($mime && $mime !== 'application/octet-stream') {
            return $mime;
        }

        $fallback = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'gif' => 'image/gif', 'webp' => 'image/webp', 'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml', 'pdf' => 'application/pdf',
            'mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo', 'mkv' => 'video/x-matroska',
            'mp3' => 'audio/mpeg', 'wav' => 'audio/wav', 'ogg' => 'audio/ogg', 'flac' => 'audio/flac',
            'txt' => 'text/plain', 'md' => 'text/markdown', 'json' => 'application/json',
            'xml' => 'application/xml', 'csv' => 'text/csv', 'css' => 'text/css',
            'js' => 'text/javascript', 'html' => 'text/plain', 'htm' => 'text/plain',
        ];

        return $fallback[$extension] ?? 'application/octet-stream';
    }

    // ==================== RENOMBRAR ====================
public function renombrar(Request $request)
{
    if (!session('loggedin')) return response()->json(['success' => false, 'message' => 'No autorizado'], 401);

    $rol = session('rol', 'secretario');
    if (!$this->tienePermiso($rol, 'renombrar')) {
        return response()->json(['success' => false, 'message' => 'No tienes permiso para renombrar elementos.'], 403);
    }

    $rutaRelativa = $request->input('ruta', '');
    $rutaValida = $this->validarRuta($rutaRelativa);
    
    if (!$rutaValida) {
        return response()->json(['success' => false, 'message' => 'Ruta no válida.'], 403);
    }
    
    $nuevoNombre = trim($request->input('nuevo_nombre', ''));
    $tipo = $request->input('tipo_elemento', '');

    if (empty($nuevoNombre)) {
        return response()->json(['success' => false, 'message' => 'Debe proporcionar un nombre válido.']);
    }

    if (!preg_match('/^[a-zA-Z0-9_\-. ]+$/', $nuevoNombre)) {
        return response()->json(['success' => false, 'message' => 'El nombre contiene caracteres no permitidos.']);
    }

    $rutaOriginal = $rutaValida['absoluta'];

    if (!file_exists($rutaOriginal)) {
        return response()->json(['success' => false, 'message' => 'La ruta especificada no existe.']);
    }

    if (realpath($rutaOriginal) === $this->basePath) {
        return response()->json(['success' => false, 'message' => 'No se puede renombrar la carpeta principal.']);
    }

    $dirPadre = dirname($rutaOriginal);
    $extension = pathinfo($rutaOriginal, PATHINFO_EXTENSION);

    if ($tipo === 'archivo' && !empty($extension)) {
        if (strtolower(pathinfo($nuevoNombre, PATHINFO_EXTENSION)) !== strtolower($extension)) {
            $nuevoNombre .= '.' . $extension;
        }
    }

    $nuevaRuta = $dirPadre . DIRECTORY_SEPARATOR . $nuevoNombre;

    if (file_exists($nuevaRuta)) {
        return response()->json(['success' => false, 'message' => 'Ya existe un elemento con ese nombre.']);
    }

    if (rename($rutaOriginal, $nuevaRuta)) {
        return response()->json(['success' => true, 'message' => 'Renombrado exitosamente.']);
    }

    return response()->json(['success' => false, 'message' => 'Error al renombrar. Verifica los permisos.']);
}

// ==================== ELIMINAR ====================
public function eliminar(Request $request)
{
    if (!session('loggedin')) return response()->json(['success' => false, 'message' => 'No autorizado'], 401);

    $rol = session('rol', 'secretario');
    if (!$this->tienePermiso($rol, 'eliminar')) {
        return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar.']);
    }

    $rutaRelativa = $request->input('ruta', '');
    $rutaValida = $this->validarRuta($rutaRelativa);
    
    if (!$rutaValida) {
        return response()->json(['success' => false, 'message' => 'Ruta no válida.'], 403);
    }
    
    $tipo = $request->input('tipo', '');
    $rutaAbsoluta = $rutaValida['absoluta'];

    if (!file_exists($rutaAbsoluta)) {
        return response()->json(['success' => false, 'message' => 'El elemento no existe.']);
    }

    if (empty($tipo)) {
        $tipo = is_dir($rutaAbsoluta) ? 'carpeta' : 'archivo';
    }

    $resultado = $this->moverAPapelera($rutaAbsoluta, $tipo);

    if ($resultado) {
        return response()->json(['success' => true, 'message' => 'Elemento movido a la papelera.']);
    }

    return response()->json(['success' => false, 'message' => 'Error al eliminar el elemento.']);
}

// ==================== PAPELERA ====================
private function getPapeleraPath()
{
    $path = storage_path('app/Papelera');
    if (!file_exists($path)) mkdir($path, 0755, true);
    return $path;
}

private function moverAPapelera($rutaAbsoluta, $tipo)
{
    $papeleraPath = $this->getPapeleraPath();
    $rutaRelativa = substr($rutaAbsoluta, strlen($this->basePath) + 1);
    $nombreUnico = date('YmdHis') . '_' . str_replace(['/', '\\'], '_', $rutaRelativa);
    $rutaDestino = $papeleraPath . DIRECTORY_SEPARATOR . $nombreUnico;

    $metadatos = [
        'ruta_original' => $rutaRelativa,
        'tipo' => $tipo,
        'fecha_eliminacion' => date('Y-m-d H:i:s'),
        'usuario' => session('username', 'desconocido')
    ];

    file_put_contents($rutaDestino . '.meta', json_encode($metadatos, JSON_PRETTY_PRINT));

    if ($tipo === 'carpeta') {
        $resultado = $this->copiarCarpetaRecursiva($rutaAbsoluta, $rutaDestino);
        if ($resultado) $this->eliminarCarpetaRecursiva($rutaAbsoluta);
        return $resultado;
    }

    return rename($rutaAbsoluta, $rutaDestino);
}

public function listarPapelera()
{
    if (!session('loggedin')) return response()->json([], 401);

    $rol = session('rol', 'secretario');
    if (!$this->tienePermiso($rol, 'papelera')) {
        return response()->json(['success' => false, 'message' => 'No tienes permiso para acceder a la papelera.'], 403);
    }

    $papeleraPath = $this->getPapeleraPath();
    $elementos = [];

    foreach (scandir($papeleraPath) as $archivo) {
        if ($archivo === '.' || $archivo === '..' || str_ends_with($archivo, '.meta')) continue;

        $rutaElemento = $papeleraPath . DIRECTORY_SEPARATOR . $archivo;
        $rutaMetadatos = $rutaElemento . '.meta';
        $metadatos = [];

        if (file_exists($rutaMetadatos)) {
            $metadatos = json_decode(file_get_contents($rutaMetadatos), true) ?? [];
        }

        $elementos[] = [
            'nombre' => $archivo,
            'nombre_original' => basename($metadatos['ruta_original'] ?? $archivo),
            'tipo' => $metadatos['tipo'] ?? (is_dir($rutaElemento) ? 'carpeta' : 'archivo'),
            'fecha_eliminacion' => $metadatos['fecha_eliminacion'] ?? date('Y-m-d H:i:s', filemtime($rutaElemento)),
            'ruta_original' => $metadatos['ruta_original'] ?? '',
            'usuario' => $metadatos['usuario'] ?? 'desconocido',
        ];
    }

    usort($elementos, fn($a, $b) => strtotime($b['fecha_eliminacion']) - strtotime($a['fecha_eliminacion']));

    return response()->json($elementos);
}

public function restaurar(Request $request)
{
    if (!session('loggedin')) return response()->json(['success' => false], 401);

    $rol = session('rol', 'secretario');
    if (!$this->tienePermiso($rol, 'restaurar')) {
        return response()->json(['success' => false, 'message' => 'No tienes permiso para restaurar elementos.'], 403);
    }

    $nombreElemento = $request->input('elemento');
    $papeleraPath = $this->getPapeleraPath();
    $rutaElemento = $papeleraPath . DIRECTORY_SEPARATOR . $nombreElemento;
    $rutaMetadatos = $rutaElemento . '.meta';

    if (!file_exists($rutaElemento) || !file_exists($rutaMetadatos)) {
        return response()->json(['success' => false, 'message' => 'Elemento no encontrado en la papelera.']);
    }

    $metadatos = json_decode(file_get_contents($rutaMetadatos), true);
    $rutaOriginal = $this->basePath . DIRECTORY_SEPARATOR . $metadatos['ruta_original'];

    if (file_exists($rutaOriginal)) {
        return response()->json(['success' => false, 'message' => 'Ya existe un elemento con ese nombre en la ubicación original.']);
    }

    $dirPadre = dirname($rutaOriginal);
    if (!file_exists($dirPadre)) mkdir($dirPadre, 0755, true);

    $resultado = false;
    if ($metadatos['tipo'] === 'carpeta') {
        $resultado = $this->copiarCarpetaRecursiva($rutaElemento, $rutaOriginal);
        if ($resultado) $this->eliminarCarpetaRecursiva($rutaElemento);
    } else {
        $resultado = rename($rutaElemento, $rutaOriginal);
    }

    if ($resultado && file_exists($rutaMetadatos)) unlink($rutaMetadatos);

    return response()->json([
        'success' => $resultado,
        'message' => $resultado ? 'Elemento restaurado correctamente.' : 'Error al restaurar el elemento.'
    ]);
}

public function vaciarPapelera()
{
    if (!session('loggedin')) return response()->json(['success' => false], 401);

    $rol = session('rol', 'secretario');
    if (!$this->tienePermiso($rol, 'vaciar_papelera')) {
        return response()->json(['success' => false, 'message' => 'No tienes permiso para vaciar la papelera.']);
    }

    $papeleraPath = $this->getPapeleraPath();

    foreach (scandir($papeleraPath) as $archivo) {
        if ($archivo === '.' || $archivo === '..') continue;
        $ruta = $papeleraPath . DIRECTORY_SEPARATOR . $archivo;
        if (is_dir($ruta)) $this->eliminarCarpetaRecursiva($ruta);
        else unlink($ruta);
    }

    return response()->json(['success' => true, 'message' => 'Papelera vaciada correctamente.']);
}

// ==================== ELIMINAR PERMANENTE (ítem individual) ====================
public function eliminarPermanente(Request $request)
{
    if (!session('loggedin')) return response()->json(['success' => false], 401);

    $rol = session('rol', 'secretario');
    if (!$this->tienePermiso($rol, 'vaciar_papelera')) {
        return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar permanentemente.'], 403);
    }

    $nombreElemento = $request->input('elemento');
    if (empty($nombreElemento)) {
        return response()->json(['success' => false, 'message' => 'Elemento no especificado.']);
    }

    $papeleraPath   = $this->getPapeleraPath();
    $rutaElemento   = $papeleraPath . DIRECTORY_SEPARATOR . $nombreElemento;
    $rutaMetadatos  = $rutaElemento . '.meta';

    // Seguridad: verificar que el elemento está dentro de la papelera
    $real = realpath($rutaElemento);
    $realPapelera = realpath($papeleraPath);
    if ($real === false || strpos($real, $realPapelera) !== 0) {
        return response()->json(['success' => false, 'message' => 'Ruta no válida.'], 403);
    }

    if (!file_exists($rutaElemento)) {
        return response()->json(['success' => false, 'message' => 'El elemento no existe en la papelera.']);
    }

    $resultado = false;
    if (is_dir($rutaElemento)) {
        $resultado = $this->eliminarCarpetaRecursiva($rutaElemento);
    } else {
        $resultado = unlink($rutaElemento);
    }

    // Borrar metadatos si existen
    if ($resultado && file_exists($rutaMetadatos)) {
        unlink($rutaMetadatos);
    }

    return response()->json([
        'success' => $resultado,
        'message' => $resultado ? 'Elemento eliminado permanentemente.' : 'Error al eliminar el elemento.',
    ]);
}

private function copiarCarpetaRecursiva($origen, $destino)
{
    if (!file_exists($destino)) mkdir($destino, 0755, true);
    $dir = opendir($origen);
    while (($archivo = readdir($dir)) !== false) {
        if ($archivo != '.' && $archivo != '..') {
            $src = $origen . DIRECTORY_SEPARATOR . $archivo;
            $dst = $destino . DIRECTORY_SEPARATOR . $archivo;
            if (is_dir($src)) $this->copiarCarpetaRecursiva($src, $dst);
            else copy($src, $dst);
        }
    }
    closedir($dir);
    return true;
}

private function eliminarCarpetaRecursiva($ruta)
{
    if (!is_dir($ruta)) return false;
    $items = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($ruta, \RecursiveDirectoryIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        if ($item->isDir()) rmdir($item->getRealPath());
        else unlink($item->getRealPath());
    }
    return rmdir($ruta);
}
// ==================== SUBIR ARCHIVOS ====================
public function vistaSubir()
{
    if (!session('loggedin')) return redirect('/');

    // $carpetas: solo nombres raíz (strings) → para el sidebar del layout
    $carpetas = [];
    if (is_dir($this->basePath)) {
        foreach (scandir($this->basePath) as $item) {
            if ($item !== '.' && $item !== '..' && is_dir($this->basePath . '/' . $item)) {
                $carpetas[] = $item;
            }
        }
    }

    // $todasLasCarpetas: array de arrays ['name','level'] → para el select de destino
    $todasLasCarpetas = $this->listarTodasLasCarpetas($this->basePath, $this->basePath);

    return view('modulos.subir', compact('carpetas', 'todasLasCarpetas'));
}

public function subirChunk(Request $request)
{
    if (!session('loggedin')) return response()->json(['success' => false, 'message' => 'No autorizado'], 401);

    if (!$this->tienePermiso(session('rol', 'secretario'), 'subir')) {
        return response()->json(['success' => false, 'message' => 'No tienes permiso para subir archivos']);
    }

    $tempDir = storage_path('app/temp');
    if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);

    if ($request->input('action') === 'complete') {
        return $this->completarSubida($request, $tempDir);
    }

    return $this->subirFragmento($request, $tempDir);
}

private function subirFragmento(Request $request, $tempDir)
{
    if (!$request->hasFile('chunk')) {
        return response()->json(['success' => false, 'message' => 'No se recibió el fragmento']);
    }

    $fileId = $request->input('fileId');
    $chunkIndex = (int)$request->input('chunkIndex');
    $totalChunks = (int)$request->input('totalChunks');
    $fileName = preg_replace('/[\\/\:\*\?\"\<\>\|]/', '', $request->input('fileName'));

    $fileDir = $tempDir . '/' . $fileId;
    if (!file_exists($fileDir)) mkdir($fileDir, 0755, true);

    $chunkPath = $fileDir . '/' . $chunkIndex;
    $request->file('chunk')->move($fileDir, $chunkIndex);

    return response()->json([
        'success' => true,
        'message' => "Fragmento $chunkIndex/$totalChunks subido correctamente",
        'chunkIndex' => $chunkIndex,
        'totalChunks' => $totalChunks
    ]);
}

private function completarSubida(Request $request, $tempDir)
{
    $fileId = $request->input('fileId');
    $fileName = preg_replace('/[\\/\:\*\?\"\<\>\|]/', '', $request->input('fileName'));
    $totalChunks = (int)$request->input('totalChunks');
    $carpetaDestino = $request->input('carpeta_destino', '');

    $fileDir = $tempDir . '/' . $fileId;

    for ($i = 0; $i < $totalChunks; $i++) {
        if (!file_exists($fileDir . '/' . $i)) {
            return response()->json(['success' => false, 'message' => "Falta el fragmento $i"]);
        }
    }

    // Construir ruta destino con validación
    $carpetaDestino = $request->input('carpeta_destino', '');
    $rutaDestinoValida = $this->validarRuta($carpetaDestino);
    
    if (!$rutaDestinoValida && !empty($carpetaDestino)) {
        return response()->json(['success' => false, 'message' => 'Ruta de destino inválida']);
    }
    
    $rutaDestino = !empty($carpetaDestino) ? $rutaDestinoValida['absoluta'] : $this->basePath;

    if (!file_exists($rutaDestino)) mkdir($rutaDestino, 0755, true);

    $rutaCompleta = $rutaDestino . DIRECTORY_SEPARATOR . $fileName;

    if (file_exists($rutaCompleta)) {
        return response()->json(['success' => false, 'message' => "Ya existe un archivo con el nombre '$fileName'"]);
    }
    
    // Validar tipo de archivo
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
    if (!$this->validarTipoArchivo($extension)) {
        return response()->json(['success' => false, 'message' => 'Tipo de archivo no permitido']);
    }

    $finalFile = fopen($rutaCompleta, 'wb');
    if (!$finalFile) {
        return response()->json(['success' => false, 'message' => 'No se pudo crear el archivo final']);
    }

    for ($i = 0; $i < $totalChunks; $i++) {
        $chunkPath = $fileDir . '/' . $i;
        fwrite($finalFile, file_get_contents($chunkPath));
        unlink($chunkPath);
    }

    fclose($finalFile);
    if (is_dir($fileDir)) rmdir($fileDir);

    $rutaRelativa = ltrim(str_replace($this->basePath, '', $rutaDestino), '/\\');

    return response()->json([
        'success' => true,
        'message' => "El archivo '$fileName' ha sido subido exitosamente",
        'fileName' => $fileName,
        'redirectUrl' => empty($rutaRelativa) ? '/dashboard' : '/dashboard?carpeta=' . urlencode($rutaRelativa)
    ]);
}

    private function listarTodasLasCarpetas($path, $basePath, $level = 0)
    {
        $carpetas = [];
        if (!is_dir($path)) return $carpetas;

        foreach (scandir($path) as $item) {
            if ($item === '.' || $item === '..') continue;
            $fullPath = $path . '/' . $item;
            if (is_dir($fullPath)) {
                $relativa = ltrim(str_replace($basePath, '', $fullPath), '/\\');
                $carpetas[] = ['name' => $relativa, 'level' => $level];
                $carpetas = array_merge($carpetas, $this->listarTodasLasCarpetas($fullPath, $basePath, $level + 1));
            }
        }
        return $carpetas;
    }

    // ==================== DESCARGAR ====================
    public function descargar(Request $request)
    {
        if (!session('loggedin')) return response('No autorizado', 401);

        $rutaRelativa = str_replace('..', '', $request->get('archivo', ''));
        $rutaValida = $this->validarRuta($rutaRelativa);
        
        if (!$rutaValida) {
            abort(403, 'Ruta no válida.');
        }
        
        $rutaAbsoluta = $rutaValida['absoluta'];

        if (!file_exists($rutaAbsoluta) || is_dir($rutaAbsoluta)) {
            abort(404, 'Archivo no encontrado.');
        }

        return response()->download($rutaAbsoluta);
    }

    // ==================== PREVISUALIZAR (inline) ====================
    public function previsualizar(Request $request)
    {
        if (!session('loggedin')) {
            return response('No autorizado', 401);
        }

        $rutaRelativa = $request->get('archivo', '');
        $rutaValida = $this->validarRuta($rutaRelativa);

        if (!$rutaValida) {
            abort(403, 'Ruta no válida.');
        }

        $rutaAbsoluta = $rutaValida['absoluta'];

        if (!file_exists($rutaAbsoluta) || is_dir($rutaAbsoluta)) {
            abort(404, 'Archivo no encontrado.');
        }

        $extension = strtolower(pathinfo($rutaAbsoluta, PATHINFO_EXTENSION));
        $preview = $this->clasificarPreview($extension);

        if (!$preview['puede'] || $preview['tipo'] === 'text') {
            abort(415, 'Tipo de archivo no previsualizable.');
        }

        $mime = $this->obtenerMimePreview($rutaAbsoluta, $extension);

        return response()->file($rutaAbsoluta, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($rutaAbsoluta) . '"',
            'Cache-Control' => 'private, max-age=60',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    // ==================== CREAR CARPETA ====================
    public function crearCarpeta(Request $request)
    {
        if (!session('loggedin')) return response()->json(['success' => false, 'message' => 'No autorizado'], 401);

        $rol = session('rol', 'secretario');
        if (!$this->tienePermiso($rol, 'crear_carpeta')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para crear carpetas.']);
        }

        $nombre      = trim($request->input('nombre', ''));
        $carpetaPadre = $request->input('carpeta_padre', '');
        
        $rutaPadreValida = $this->validarRuta($carpetaPadre);
        if (!$rutaPadreValida && !empty($carpetaPadre)) {
            return response()->json(['success' => false, 'message' => 'Ruta de carpeta padre no válida.']);
        }

        if (empty($nombre) || !preg_match('/^[a-zA-Z0-9_\-. ]+$/', $nombre)) {
            return response()->json(['success' => false, 'message' => 'Nombre de carpeta inválido.']);
        }

        $rutaBase  = !empty($carpetaPadre) ? $rutaPadreValida['absoluta'] : $this->basePath;
        $nuevaRuta = $rutaBase . DIRECTORY_SEPARATOR . $nombre;

        if (file_exists($nuevaRuta)) {
            return response()->json(['success' => false, 'message' => 'Ya existe una carpeta con ese nombre.']);
        }

        if (mkdir($nuevaRuta, 0755, true)) {
            return response()->json(['success' => true, 'message' => 'Carpeta "' . $nombre . '" creada correctamente.']);
        }

        return response()->json(['success' => false, 'message' => 'Error al crear la carpeta.']);
    }

    // ==================== CREAR ARCHIVO ====================
    public function crearArchivo(Request $request)
    {
        if (!session('loggedin')) return response()->json(['success' => false, 'message' => 'No autorizado'], 401);

        $nombre         = trim($request->input('nombre', ''));
        $contenido      = $request->input('contenido', '');
        $carpetaDestino = $request->input('carpeta_destino', '');
        
        $rutaDestinoValida = $this->validarRuta($carpetaDestino);
        if (!$rutaDestinoValida && !empty($carpetaDestino)) {
            return response()->json(['success' => false, 'message' => 'Ruta de destino no válida.']);
        }

        if (empty($nombre) || !preg_match('/^[a-zA-Z0-9_\-. ]+$/', $nombre)) {
            return response()->json(['success' => false, 'message' => 'Nombre de archivo inválido.']);
        }
        
        // Validar tipo de archivo
        $extension = pathinfo($nombre, PATHINFO_EXTENSION);
        if (!empty($extension) && !$this->validarTipoArchivo($extension)) {
            return response()->json(['success' => false, 'message' => 'Tipo de archivo no permitido.']);
        }

        $rutaBase    = !empty($carpetaDestino) ? $rutaDestinoValida['absoluta'] : $this->basePath;
        $rutaArchivo = $rutaBase . DIRECTORY_SEPARATOR . $nombre;

        if (file_exists($rutaArchivo)) {
            return response()->json(['success' => false, 'message' => 'Ya existe un archivo con ese nombre.']);
        }

        if (file_put_contents($rutaArchivo, $contenido) !== false) {
            return response()->json(['success' => true, 'message' => 'Archivo "' . $nombre . '" creado correctamente.']);
        }

        return response()->json(['success' => false, 'message' => 'Error al crear el archivo.']);
    }

    // ==================== CONTENIDO ARCHIVO (para editar) ====================
    public function contenidoArchivo(Request $request)
    {
        if (!session('loggedin')) return response()->json(['success' => false], 401);

        $rutaRelativa = $request->get('ruta', '');
        $rutaValida = $this->validarRuta($rutaRelativa);
        
        if (!$rutaValida) {
            return response()->json(['success' => false, 'message' => 'Ruta no válida.']);
        }
        
        $rutaAbsoluta = $rutaValida['absoluta'];

        if (!file_exists($rutaAbsoluta) || is_dir($rutaAbsoluta)) {
            return response()->json(['success' => false, 'message' => 'Archivo no encontrado.']);
        }

        $extension = strtolower(pathinfo($rutaAbsoluta, PATHINFO_EXTENSION));
        $preview = $this->clasificarPreview($extension);
        $esPreview = $request->boolean('preview');

        if ($esPreview && $preview['tipo'] !== 'text') {
            return response()->json(['success' => false, 'message' => 'Este archivo no admite vista previa de texto.']);
        }

        $maxBytes = $esPreview ? 200 * 1024 : PHP_INT_MAX;
        $fileSize = filesize($rutaAbsoluta);
        $truncado = $fileSize > $maxBytes;

        $handle = fopen($rutaAbsoluta, 'rb');
        $contenido = $handle ? fread($handle, $maxBytes) : '';
        if ($handle) {
            fclose($handle);
        }

        return response()->json([
            'success'    => true,
            'contenido'  => $contenido,
            'nombre'     => basename($rutaAbsoluta),
            'extension'  => $extension,
            'truncado'   => $truncado,
            'tamano'     => $fileSize,
        ]);
    }

    // ==================== GUARDAR ARCHIVO ====================
    public function guardarArchivo(Request $request)
    {
        if (!session('loggedin')) return response()->json(['success' => false], 401);

        $rol = session('rol', 'secretario');
        if (!$this->tienePermiso($rol, 'crear_archivo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para editar archivos.'], 403);
        }

        $rutaRelativa = $request->input('ruta', '');
        $contenido    = $request->input('contenido', '');
        
        $rutaValida = $this->validarRuta($rutaRelativa);
        if (!$rutaValida) {
            return response()->json(['success' => false, 'message' => 'Ruta no válida.']);
        }
        
        $rutaAbsoluta = $rutaValida['absoluta'];

        if (!file_exists($rutaAbsoluta) || is_dir($rutaAbsoluta)) {
            return response()->json(['success' => false, 'message' => 'Archivo no encontrado.']);
        }

        if (file_put_contents($rutaAbsoluta, $contenido) !== false) {
            return response()->json(['success' => true, 'message' => 'Archivo guardado correctamente.']);
        }

        return response()->json(['success' => false, 'message' => 'Error al guardar el archivo.']);
    }

    // ==================== COPIAR ====================
    public function copiar(Request $request)
    {
        if (!session('loggedin')) return response()->json(['success' => false], 401);

        $rol = session('rol', 'secretario');
        if (!$this->tienePermiso($rol, 'copiar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para copiar.']);
        }

        $rutaOrigen     = $request->input('ruta_origen', '');
        $carpetaDestino = $request->input('carpeta_destino', '');
        
        $rutaOrigenValida = $this->validarRuta($rutaOrigen);
        $rutaDestinoValida = $this->validarRuta($carpetaDestino);
        
        if (!$rutaOrigenValida) {
            return response()->json(['success' => false, 'message' => 'Ruta de origen no válida.']);
        }
        if (!$rutaDestinoValida && !empty($carpetaDestino)) {
            return response()->json(['success' => false, 'message' => 'Ruta de destino no válida.']);
        }

        $origen       = $rutaOrigenValida['absoluta'];
        $destino      = !empty($carpetaDestino) ? $rutaDestinoValida['absoluta'] : $this->basePath;
        $destinoFinal = $destino . DIRECTORY_SEPARATOR . basename($origen);

        if (!file_exists($origen)) {
            return response()->json(['success' => false, 'message' => 'El elemento origen no existe.']);
        }
        if (file_exists($destinoFinal)) {
            return response()->json(['success' => false, 'message' => 'Ya existe un elemento con ese nombre en el destino.']);
        }

        $resultado = is_dir($origen)
            ? $this->copiarCarpetaRecursiva($origen, $destinoFinal)
            : copy($origen, $destinoFinal);

        return response()->json([
            'success' => $resultado,
            'message' => $resultado ? 'Elemento copiado correctamente.' : 'Error al copiar el elemento.',
        ]);
    }

    // ==================== MOVER ====================
    public function mover(Request $request)
    {
        if (!session('loggedin')) return response()->json(['success' => false], 401);

        $rol = session('rol', 'secretario');
        if (!$this->tienePermiso($rol, 'mover')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para mover.']);
        }

        $rutaOrigen     = $request->input('ruta_origen', '');
        $carpetaDestino = $request->input('carpeta_destino', '');
        
        $rutaOrigenValida = $this->validarRuta($rutaOrigen);
        $rutaDestinoValida = $this->validarRuta($carpetaDestino);
        
        if (!$rutaOrigenValida) {
            return response()->json(['success' => false, 'message' => 'Ruta de origen no válida.']);
        }
        if (!$rutaDestinoValida && !empty($carpetaDestino)) {
            return response()->json(['success' => false, 'message' => 'Ruta de destino no válida.']);
        }

        $origen       = $rutaOrigenValida['absoluta'];
        $destino      = !empty($carpetaDestino) ? $rutaDestinoValida['absoluta'] : $this->basePath;
        $destinoFinal = $destino . DIRECTORY_SEPARATOR . basename($origen);

        if (!file_exists($origen)) {
            return response()->json(['success' => false, 'message' => 'El elemento origen no existe.']);
        }
        if (file_exists($destinoFinal)) {
            return response()->json(['success' => false, 'message' => 'Ya existe un elemento con ese nombre en el destino.']);
        }

        if (rename($origen, $destinoFinal)) {
            return response()->json(['success' => true, 'message' => 'Elemento movido correctamente.']);
        }

        return response()->json(['success' => false, 'message' => 'Error al mover el elemento.']);
    }
    // ==================== OBTENER CONTENIDO PARA SIDEBAR (árbol) ====================
    public function obtenerSubcarpetas(Request $request)
    {
        if (!session('loggedin')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
        }

        $carpetaPadre = urldecode($request->query('carpeta', ''));
        $carpetaPadre = str_replace('+', ' ', $carpetaPadre);

        if (empty($carpetaPadre)) {
            $rutaAbsoluta = $this->basePath;
        } else {
            $rutaValida = $this->validarRuta($carpetaPadre);
            if (!$rutaValida) {
                return response()->json(['success' => false, 'message' => 'Ruta no válida'], 403);
            }
            $rutaAbsoluta = $rutaValida['absoluta'];
        }

        $carpetas = [];
        $archivos = [];
        $limiteArchivos = 80;

        if (is_dir($rutaAbsoluta)) {
            $items = scandir($rutaAbsoluta);
            sort($items);

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $rutaItem = $rutaAbsoluta . DIRECTORY_SEPARATOR . $item;
                $rutaRelativa = empty($carpetaPadre) ? $item : $carpetaPadre . '/' . $item;

                if (is_dir($rutaItem)) {
                    $carpetas[] = [
                        'nombre' => $item,
                        'ruta' => $rutaRelativa,
                        'tiene_hijos' => $this->carpetaTieneHijos($rutaItem),
                        'tiene_subcarpetas' => $this->tieneSubcarpetas($rutaItem),
                    ];
                } elseif (count($archivos) < $limiteArchivos) {
                    $extension = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                    $preview = $this->clasificarPreview($extension);
                    $archivos[] = [
                        'nombre' => $item,
                        'ruta' => $rutaRelativa,
                        'icono' => $this->getIcono($extension),
                        'tipoPreview' => $preview['tipo'],
                        'puedePrevisualizar' => $preview['puede'],
                        'size' => $this->formatSize(filesize($rutaItem)),
                    ];
                }
            }
        }

        $totalArchivos = 0;
        if (is_dir($rutaAbsoluta)) {
            foreach (scandir($rutaAbsoluta) as $item) {
                if ($item !== '.' && $item !== '..' && is_file($rutaAbsoluta . DIRECTORY_SEPARATOR . $item)) {
                    $totalArchivos++;
                }
            }
        }

        // Compatibilidad con subcarpetas.js (nombre antiguo del campo)
        $subcarpetas = array_map(function ($c) {
            return [
                'nombre' => $c['nombre'],
                'ruta' => $c['ruta'],
                'tiene_subcarpetas' => $c['tiene_subcarpetas'],
                'tiene_hijos' => $c['tiene_hijos'],
            ];
        }, $carpetas);

        return response()->json([
            'success' => true,
            'carpetas' => $carpetas,
            'subcarpetas' => $subcarpetas,
            'archivos' => $archivos,
            'total_archivos' => $totalArchivos,
            'archivos_truncados' => $totalArchivos > count($archivos),
        ]);
    }

    // ==================== BUSCAR ====================
    public function buscar(Request $request)
    {
        if (!session('loggedin')) return response()->json(['resultados' => []], 401);

        $termino = trim($request->get('q', ''));

        if (empty($termino)) {
            return response()->json(['resultados' => [], 'total' => 0]);
        }

        $resultados = [];
        $this->buscarRecursivo($this->basePath, $termino, '', $resultados);

        return response()->json(['resultados' => $resultados, 'total' => count($resultados)]);
    }

    private function buscarRecursivo($ruta, $termino, $rutaRelativa, &$resultados)
    {
        foreach (scandir($ruta) as $item) {
            if ($item === '.' || $item === '..') continue;

            $rutaCompleta = $ruta . DIRECTORY_SEPARATOR . $item;
            $rutaItem     = $rutaRelativa ? $rutaRelativa . '/' . $item : $item;

            if (stripos($item, $termino) !== false) {
                $resultados[] = [
                    'nombre' => $item,
                    'ruta'   => $rutaItem,
                    'tipo'   => is_dir($rutaCompleta) ? 'carpeta' : 'archivo',
                    'size'   => is_dir($rutaCompleta) ? '—' : $this->formatSize(filesize($rutaCompleta)),
                    'fecha'  => date('d/m/Y H:i', filemtime($rutaCompleta)),
                    'icono'  => is_dir($rutaCompleta) ? 'fa-folder' : $this->getIcono(strtolower(pathinfo($item, PATHINFO_EXTENSION))),
                ];
            }

            if (is_dir($rutaCompleta)) {
                $this->buscarRecursivo($rutaCompleta, $termino, $rutaItem, $resultados);
            }
        }
    }

    private function tieneSubcarpetas($ruta)
    {
        if (!is_dir($ruta)) {
            return false;
        }

        try {
            foreach (scandir($ruta) as $item) {
                if ($item !== '.' && $item !== '..' && is_dir($ruta . '/' . $item)) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    private function carpetaTieneHijos($ruta)
    {
        if (!is_dir($ruta)) {
            return false;
        }

        try {
            foreach (scandir($ruta) as $item) {
                if ($item !== '.' && $item !== '..') {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }
}