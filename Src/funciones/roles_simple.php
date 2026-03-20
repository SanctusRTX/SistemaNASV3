<?php
/**
 * Sistema de roles simplificado para el Sistema NAS
 * Esta implementación no requiere tablas adicionales en la base de datos
 */

// Definición de roles y sus permisos
$GLOBALS['roles_permisos'] = [
    'administrador' => [
        'subir' => true,
        'descargar' => true,
        'crear_archivo' => true,
        'crear_carpeta' => true,
        'eliminar' => true,
        'renombrar' => true,
        'mover' => true,
        'copiar' => true,
        'papelera' => true,
        'restaurar' => true,
        'vaciar_papelera' => true
    ],
    'supervisor' => [
        'subir' => true,
        'descargar' => true,
        'crear_archivo' => true,
        'crear_carpeta' => true,
        'eliminar' => false, // No puede eliminar carpetas
        'renombrar' => true,
        'mover' => true,
        'copiar' => true,
        'papelera' => false,
        'restaurar' => false,
        'vaciar_papelera' => false
    ],
    'secretario' => [
        'subir' => true,
        'descargar' => true,
        'crear_archivo' => true,
        'crear_carpeta' => false,
        'eliminar' => false,
        'renombrar' => false,
        'mover' => false,
        'copiar' => false,
        'papelera' => false,
        'restaurar' => false,
        'vaciar_papelera' => false
    ]
];

/**
 * Asigna un rol a un usuario en la sesión
 * @param string $username Nombre de usuario
 * @param string $rol Rol a asignar (administrador, supervisor, secretario)
 * @return void
 */
function asignarRol($username, $rol = 'administrador') {
    // Verificar que el rol sea válido
    if (!isset($GLOBALS['roles_permisos'][$rol])) {
        $rol = 'administrador'; // Rol por defecto si el rol especificado no existe
    }
    
    // Asignar el rol en la sesión
    $_SESSION['rol'] = $rol;
    
    // Registrar en el log
    error_log("Rol asignado a usuario $username: $rol");
}

/**
 * Verifica si el usuario tiene permiso para realizar una acción específica
 * @param string $accion Acción a verificar (subir, descargar, crear, eliminar, etc.)
 * @return bool True si tiene permiso, False si no
 */
function tienePermiso($accion) {
    // Si no hay sesión iniciada, no tiene permiso
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        return false;
    }
    
    // Si no hay rol definido, asumimos que es administrador (para compatibilidad con versiones anteriores)
    if (!isset($_SESSION['rol'])) {
        $_SESSION['rol'] = 'administrador';
    }
    
    $rol = $_SESSION['rol'];
    
    // Verificar si el rol y la acción existen en la matriz de permisos
    if (isset($GLOBALS['roles_permisos'][$rol]) && isset($GLOBALS['roles_permisos'][$rol][$accion])) {
        return $GLOBALS['roles_permisos'][$rol][$accion];
    }
    
    // Por defecto, no tiene permiso
    return false;
}

/**
 * Verifica si el usuario tiene un rol específico
 * @param string $rol Rol a verificar
 * @return bool True si tiene el rol, False si no
 */
function tieneRol($rol) {
    // Si no hay sesión iniciada, no tiene rol
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        return false;
    }
    
    // Verificar si el rol del usuario coincide con el rol solicitado
    return isset($_SESSION['rol']) && $_SESSION['rol'] === $rol;
}

/**
 * Obtiene el nombre del rol del usuario actual
 * @return string Nombre del rol o 'invitado' si no hay sesión
 */
function obtenerRolUsuario() {
    // Si no hay sesión iniciada, es invitado
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        return 'invitado';
    }
    
    // Devolver el rol del usuario o 'invitado' si no hay rol definido
    return isset($_SESSION['rol']) ? $_SESSION['rol'] : 'invitado';
}

/**
 * Redirecciona al usuario si no tiene permiso para realizar una acción
 * @param string $accion Acción a verificar
 * @param string $urlRedireccion URL a la que redirigir si no tiene permiso
 * @return void
 */
function verificarPermiso($accion, $urlRedireccion = '../index.php?error=permiso') {
    if (!tienePermiso($accion)) {
        // Establecer mensaje de error
        $_SESSION['mensaje'] = 'No tienes permiso para realizar esta acción.';
        $_SESSION['tipo_mensaje'] = 'error';
        
        // Redirigir
        header('Location: ' . $urlRedireccion);
        exit();
    }
}
?>
