<?php
// Reporte de errores para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';
$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];

    try {
        // Buscamos al usuario por correo
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch();

        // Verificamos si existe y si la contraseña coincide
        if ($usuario && password_verify($password, $usuario->password_hash)) {
            $_SESSION['usuario_id'] = $usuario->id;
            $_SESSION['nombre_completo'] = $usuario->nombre_completo;
            header("Location: bienvenida.php");
            exit();
        } else {
            $mensaje = "<div class='alert alert-danger'>Credenciales incorrectas.</div>";
        }
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger'>Error en el login: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Encuestas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-dark text-white text-center">
                    <h4>Iniciar Sesión</h4>
                </div>
                <div class="card-body">
                    <?php echo $mensaje; ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="correo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Ingresar</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="registro.php">¿No tienes cuenta? Regístrate</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>