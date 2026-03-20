<?php
$dsn = 'mysql:host=localhost;dbname=gob_bd3;charset=utf8mb4';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT id, password FROM ss_usuarios";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($usuarios as $usuario) {
        $hashedPassword = password_hash($usuario['password'], PASSWORD_DEFAULT);

        $updateSql = "UPDATE ss_usuarios SET password = :password WHERE id = :id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $updateStmt->bindParam(':id', $usuario['id'], PDO::PARAM_INT);
        $updateStmt->execute();
    }

    echo "Contraseñas actualizadas exitosamente";
} catch (PDOException $e) {
    echo "Error en la actualización: " . $e->getMessage();
}
?>