<?php 
if(isset($_SESSION) && !empty($_SESSION) && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true){
    header("location: ./Src/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistemas NAS</title>
    <link rel="stylesheet" href="./Public/Css/style.css">
    <style>
        .error-message {
            color: #ff3333;
            background-color: #ffe6e6;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="container-form">
            <form action="./Src/funciones/validacion.php" method="post" class="sign-in">
                <h2>Iniciar Sesión</h2>
                <?php if(isset($_GET['error']) && $_GET['error'] == 1): ?>
                    <div class="error-message">Usuario o contraseña incorrectos</div>
                <?php endif; ?>
                <span>Use su usuario y contraseña </span>
                <div class="container-input">
                    <ion-icon name="mail-outline"></ion-icon>
                    <input type="text" name="usuario" placeholder="usuario">
                </div>
                <div class="container-input">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" name="password" placeholder="contraseña">
                </div>
                <button class="button">INICIAR SESIÓN</button>
            </form>
        </div>
        <div class="container-form">
           <form class="sign-up">
            <h2>Registrarse</h2>
            <div class="social-networks">
                <ion-icon name="logo-twitter"></ion-icon>
                <ion-icon name="logo-instagram"></ion-icon>
                <ion-icon name="logo-youtube"></ion-icon>
            </div>
            <span>Use su correo electrónico para registrase</span>
            <div class="container-input">
                <ion-icon name="person-outline"></ion-icon>
                <input type="text" placeholder="Nombre">
            </div>
            <div class="container-input">
                <ion-icon name="mail-outline"></ion-icon>
                <input type="text" placeholder="Email">
            </div>
            <div class="container-input">
                <ion-icon name="lock-closed-outline"></ion-icon>
                <input type="password" placeholder="contraseña">
            </div>
            <button class="button">REGISTRARSE</button>
           </form> 
        </div>

        <div class=" container-welcome">
        <div class="welcome-sign-up welcome">
            <h3>¡Bienvenido!</h3>
            <p>Ingrese sus datos personales para usar las funciones del sitio</p>
        </div>
        <div class="welcome-sign-in welcome">
            <h3>¡Hola!</h3>
            <p>Regístrese con sus datos personales para usar las funciones del sitio</p>
            <button class="button" id="btn-sign-in">Iniciar Sesión</button>
            </div>
        </div>

    
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
<script src="./Public/js/script.js"></script>
</html> 