<?php
function createDirectory($dirPath) {
    global $pdo;

    if (empty($dirPath)) {
        echo "La ruta de la carpeta no puede estar vacía.<br>";
        return;
    }

    echo "Verificando existencia de la carpeta: " . htmlspecialchars($dirPath) . "<br>";

    if (!file_exists($dirPath)) {
        if (mkdir($dirPath, 0777, true)) {
            echo "Carpeta creada: " . htmlspecialchars($dirPath) . "<br>";
            $nombre = basename($dirPath);
            $ruta = realpath($dirPath);

            try {
                echo "Insertando en la base de datos...<br>";
                $stmt = $pdo->prepare("INSERT INTO carpetas (nombre, ruta) VALUES (:nombre, :ruta)");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':ruta', $ruta);

                if ($stmt->execute()) {
                    echo "Carpeta creada exitosamente y registrada en la base de datos.<br>";
                } else {
                    echo "Error al registrar la carpeta en la base de datos.<br>";
                }
            } catch (PDOException $e) {
                echo "Error en la base de datos: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "Error al crear la carpeta en el sistema de archivos.<br>";
        }
    } else {
        echo "La carpeta ya existe.<br>";
    }
}
?>