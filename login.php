<?php
session_start();

/* ---------------------- */
/* CONEXIÓN A LA BASE DE DATOS */
$host = "localhost";
$dbname = "cun";
$username_db = "root"; // Cambiar según tu configuración
$password_db = "";     // Cambiar según tu configuración

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

/* ---------------------- */
/* VARIABLES */
$error = "";
$success = "";
$showLoginForm = false;

/* ---------------------- */
/* VERIFICAR USUARIO (AJAX) */
if(isset($_POST['action']) && $_POST['action'] === 'check_user' && isset($_POST['username'])) {
    $usuario = $conn->real_escape_string($_POST['username']);
    $checkUser = $conn->query("SELECT * FROM usuarios WHERE usuario='$usuario'");
    if ($checkUser->num_rows > 0) {
        echo "Ya se encuentra un usuario con ese nombre de usuario.";
    } else {
        echo "Disponible";
    }
    exit;
}

/* ---------------------- */
/* REGISTRO */
if (isset($_POST['action']) && $_POST['action'] === 'register') {
    $nombre = trim($conn->real_escape_string($_POST['nombre']));
    $apellido = trim($conn->real_escape_string($_POST['apellido']));
    $usuario = trim($conn->real_escape_string($_POST['username']));
    $password = trim($_POST['password']);

    // Validar contraseña segura (será reforzado por JS en tiempo real)
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{7,}$/', $password)) {
        $error = "La contraseña no cumple con los requisitos de seguridad.";
    } else {
        $checkUser = $conn->query("SELECT * FROM usuarios WHERE usuario='$usuario'");
        if ($checkUser->num_rows > 0) {
            $error = "Ya se encuentra un usuario registrado con ese usuario.";
        } else {
            $checkFull = $conn->query("SELECT * FROM usuarios WHERE nombre='$nombre' AND apellido='$apellido' AND usuario='$usuario'");
            if ($checkFull->num_rows > 0) {
                $error = "Ya hay un usuario creado con esas mismas características.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $insert = $conn->query("INSERT INTO usuarios (nombre, apellido, usuario, contraseña) VALUES ('$nombre','$apellido','$usuario','$passwordHash')");
                if ($insert) {
                    $success = "Registro exitoso. Ahora inicia sesión.";
                    $showLoginForm = true;
                } else {
                    $error = "Error al registrar usuario.";
                }
            }
        }
    }
}

/* ---------------------- */
/* LOGIN */
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $usuario = trim($conn->real_escape_string($_POST['username']));
    $password = trim($_POST['password']);

    $query = $conn->query("SELECT * FROM usuarios WHERE usuario='$usuario'");
    if ($query->num_rows === 1) {
        $row = $query->fetch_assoc();
        if(password_verify($password, $row['contraseña'])){
            $_SESSION['usuario'] = $usuario;
            header("Location: la_calidad.php");
            exit;
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login & Registro</title>
<link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="container <?php if(isset($_POST['action']) && $_POST['action'] === 'register' && !$showLoginForm) echo 'register-active'; ?>">

    <div class="form-panel">

        <!-- LOGIN -->
        <form id="login-form" class="form <?php if(!isset($_POST['action']) || $_POST['action'] === 'login' || $showLoginForm) echo 'active'; ?>" action="" method="POST">
            <h2>Bienvenido</h2>
            <?php 
            if($error && $_POST['action'] === 'login') echo "<p style='color:red;'>$error</p>"; 
            if($success && $showLoginForm) echo "<p style='color:green;'>$success</p>"; 
            ?>
            <input type="text" name="username" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="hidden" name="action" value="login">
            <button type="submit">Iniciar Sesión</button>
            <p class="switch-form">¿No tienes cuenta? <button type="button" id="show-register">Regístrate aquí</button></p>
        </form>

        <!-- REGISTRO -->
        <form id="register-form" class="form <?php if(isset($_POST['action']) && $_POST['action'] === 'register' && !$showLoginForm) echo 'active'; ?>" action="" method="POST">
            <h2>Crear Cuenta</h2>
            <?php if($error && $_POST['action'] === 'register') echo "<p style='color:red;'>$error</p>"; ?>
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="text" name="apellido" placeholder="Apellido" required>
            <input type="text" id="reg-username" name="username" placeholder="Crea tu usuario" required>
            <div id="user-status" style="color:red; font-size:14px; margin-bottom:5px;"></div>
            <input type="password" id="reg-password" name="password" placeholder="Contraseña" required>
            <div id="password-status" style="color:red; font-size:14px; margin-bottom:10px;"></div>
            <input type="hidden" name="action" value="register">
            <button type="submit" id="register-btn">Registrarse</button>
            <p class="switch-form">¿Ya tienes cuenta? <button type="button" id="show-login">Inicia sesión</button></p>
        </form>

    </div>

    <div class="info-panel">
        <h2>Aprende acerca de la calidad de software</h2>
        <p>Diviértete y aprende sobre buenas prácticas, estándares y cómo mejorar tus proyectos.</p>
    </div>

</div>

<script>
const showRegister = document.getElementById('show-register');
const showLogin = document.getElementById('show-login');
const loginForm = document.getElementById('login-form');
const registerForm = document.getElementById('register-form');
const container = document.querySelector('.container');

showRegister.addEventListener('click', () => {
    container.classList.add('register-active');
    loginForm.classList.remove('active');
    registerForm.classList.add('active');
});

showLogin.addEventListener('click', () => {
    container.classList.remove('register-active');
    registerForm.classList.remove('active');
    loginForm.classList.add('active');
});

// Verificación usuario en tiempo real
const usernameInput = document.getElementById('reg-username');
const userStatus = document.getElementById('user-status');
const registerBtn = document.getElementById('register-btn');

usernameInput.addEventListener('keyup', () => {
    const username = usernameInput.value.trim();
    if(username.length > 0){
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function(){
            if(this.responseText !== "Disponible"){
                userStatus.textContent = this.responseText;
                registerBtn.disabled = true;
            } else {
                userStatus.textContent = "";
                registerBtn.disabled = false;
            }
        };
        xhr.send("action=check_user&username=" + encodeURIComponent(username));
    } else {
        userStatus.textContent = "";
        registerBtn.disabled = false;
    }
});

// Validación contraseña en tiempo real
const passwordInput = document.getElementById('reg-password');
const passwordStatus = document.getElementById('password-status');

passwordInput.addEventListener('keyup', () => {
    const pass = passwordInput.value;
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{7,}$/;
    if(!regex.test(pass)){
        passwordStatus.textContent = "La contraseña debe tener mínimo 7 caracteres, incluir mayúscula, minúscula, número y símbolo especial.";
        registerBtn.disabled = true;
    } else {
        passwordStatus.textContent = "";
        registerBtn.disabled = false;
    }
});
</script>
</body>
</html>

