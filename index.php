<?php
session_start();
require_once './Src/bd/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['usuario'];
    $password = $_POST['password'];
}

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header('Location: ./vistas/admin/login/login.php');
    exit();
}

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: ./Src/index.php');
    exit();
} else {
    require_once './Src/vistas/admin/login/login.php';
}
?>