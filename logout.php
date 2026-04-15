<?php
// Iniciamos la sesión para poder acceder a ella y destruirla
session_start();

// 1. Vaciamos el array de sesión. Borra todos los datos guardados en memoria.
$_SESSION = array();

// 2. Borramos la "cookie" del navegador web que mantiene viva la sesión.
// Si el servidor usa cookies para las sesiones (que es lo estándar), pedimos los parámetros
// y configuramos una cookie igual pero con una fecha de expiración en el pasado (time() - 42000).
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finalmente, destruimos la sesión por completo en el servidor.
session_destroy();

// 4. Redirigimos al usuario a la página de login.
header("Location: login.php");
exit();
?>