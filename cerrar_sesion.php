<?php
session_start();

// Vaciar variables de sesión
$_SESSION = [];

// Eliminar la cookie de sesión (opcional pero más limpio)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destruir la sesión
session_destroy();

// Redirigir a la página de inicio
header("Location: index.php");
exit;
