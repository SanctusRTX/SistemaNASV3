<?php
require_once './bd/db.php';
function cerrarSesion() {
    session_start();
    session_destroy();
    header("Location: ./index.php");
    exit();
}

function uploadFile($sourcePath, $destinationFolder) {
    global $pdo;

    if (!file_exists($destinationFolder)) {
        mkdir($destinationFolder, 0777, true);
    }

    $destinationPath = $destinationFolder . '/' . basename($_FILES['archivo']['name']);
    
    if (move_uploaded_file($sourcePath, $destinationPath)) {
        $nombre = basename($_FILES['archivo']['name']);
        $peso = filesize($destinationPath);
        $ruta = $destinationPath;

        $stmt = $pdo->prepare("INSERT INTO archivos (nombre, tamano, ruta) VALUES (:nombre, :tamano, :ruta)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':tamano', $peso);
        $stmt->bindParam(':ruta', $ruta);

        if($stmt->execute()) {
            echo "Archivo subido exitosamente a $destinationFolder y registrado en la base de datos.";
        } else {
            echo "Error al registrar el archivo en la base de datos.";
        }
    } else {
        echo "Error al subir el archivo.";
    }
}
function createFile($filePath, $content) {
    global $pdo;

    if (file_put_contents($filePath, $content) !== false) {
        $nombre = basename($filePath);
        $peso = filesize($filePath);
        $ruta = $filePath;

        $stmt = $pdo->prepare("INSERT INTO archivos (nombre, tamano, ruta) VALUES (:nombre, :tamano, :ruta)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':tamano', $peso);
        $stmt->bindParam(':ruta', $ruta);
        $stmt->execute();

        echo "Archivo creado exitosamente.";
    } else {
        echo "Error al crear el archivo.";
    }
}

function editFile($filePath, $content) {
    if (file_exists($filePath) && file_put_contents($filePath, $content) !== false) {
        echo "Archivo editado exitosamente.";
    } else {
        echo "Error al editar el archivo.";
    }
}

function deleteFile($filePath) {
    global $pdo;

    if (file_exists($filePath)) {
        unlink($filePath);
        $stmt = $pdo->prepare("DELETE FROM archivos WHERE ruta = :ruta");
        $stmt->bindParam(':ruta', $filePath);
        $stmt->execute();

        echo "Archivo eliminado exitosamente: $filePath";
        
    } else {
        echo "El archivo no existe: $filePath";
    }
}

function listDirectories($dir) {
    $result = array();
    $cdir = scandir($dir);
    foreach ($cdir as $key => $value) {
        if (!in_array($value, array(".", ".."))) {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                $result[] = $value;
            }
        }
    }
    return $result;
}
function listAllDirectories($dir, &$result = array(), $level = 0) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item != "." && $item != ".." && is_dir($dir . '/' . $item)) {
            $result[] = array(
                'name' => $dir . '/' . $item,
                'level' => $level
            );
            listAllDirectories($dir . '/' . $item, $result, $level + 1);
        }
    }
    return $result;
}
function getFiles() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM archivos");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getFilesInDirectory($dir) {
    $result = array();
    $files = scandir($dir);
    foreach ($files as $file) {
        if (!in_array($file, array(".", ".."))) {
            if (is_file($dir . DIRECTORY_SEPARATOR . $file)) {
                $result[] = array(
                    'nombre' => $file,
                    'ruta' => $dir . DIRECTORY_SEPARATOR . $file,
                    'tamano' => filesize($dir . DIRECTORY_SEPARATOR . $file)
                );
            }
        }
    }
    return $result;
}
function getFilesFromDatabase() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM archivos");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getFilesAndDirectories($dir) {
    $result = array();
    $items = scandir($dir);
    foreach ($items as $item) {
        if (!in_array($item, array(".", ".."))) {
            $result[] = $item;
        }
    }
    return $result;
}

function searchFiles($dir, $searchTerm, $parent = "") {
    $dir = rtrim($dir, '/') . '/'; 
    $result = array();
    $items = scandir($dir);
    foreach ($items as $item) {
        if (!in_array($item, array(".", ".."))) {
            $path = $dir . '/' . $item;
            if (stripos($item, $searchTerm) !== false && !is_dir($path)) {
                $result[] = array(
                    'name' => $item,
                    'path' => $path,
                    'parent' => $parent
                );
            }
            if (is_dir($path)) {
                $result = array_merge($result, searchFiles($path, $searchTerm, $item));
            }
        }
    }
    return $result;
}
?>