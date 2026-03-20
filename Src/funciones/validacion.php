<?php
// Iniciar sesión solo si no hay una sesión activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivo de roles
require_once __DIR__ . '/roles_simple.php';

// Usar ruta absoluta para evitar problemas con inclusiones desde diferentes ubicaciones
$rutaBase = $_SERVER['DOCUMENT_ROOT'] . '/Sistema-NASv3/Src/';
include_once $rutaBase . 'bd/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $pdo;
    if (!$pdo) {
        die('Error: $pdo no está inicializado correctamente');
    }

    $username = $_POST["usuario"];
    $password = $_POST["password"];

    // Consulta simple para obtener el usuario
    $sql = "SELECT * FROM ss_usuarios WHERE username = :username";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Guardar información de la sesión
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        // Asignar rol según el usuario
        if ($username === 'admin') {
            asignarRol($username, 'administrador');
        } elseif ($username === 'supervisor') {
            asignarRol($username, 'supervisor');
        } elseif ($username === 'secretario') {
            asignarRol($username, 'secretario');
        } else {
            asignarRol($username, 'administrador'); // Por defecto
        }

        $_SESSION['user_id'] = $user['id'];
        
        // El rol ya ha sido asignado por la función asignarRol() más arriba
        
        // Registrar el inicio de sesión en el archivo de error (log del sistema)
        error_log('Inicio de sesión exitoso para el usuario ' . $username . ' con rol: ' . $_SESSION['rol']);
        
        header('location:../index.php');
        exit();
    } else {
        // Redirigir con mensaje de error
        header('location:../../vistas/admin/login/login.php?error=1');
        exit();
    }
}
?>