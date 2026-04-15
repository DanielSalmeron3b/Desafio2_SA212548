<?php
// Reporte de errores forzado
/*error_reporting(E_ALL);
ini_set('display_errors', 1);
*/
// Requerimos el archivo de conexión. 'require' detendrá el script si db.php no existe.
require 'db.php';
$mensaje = '';

// Verificamos si el formulario fue enviado haciendo clic en el botón (método POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // trim() elimina los espacios en blanco al inicio y al final de lo que escribió el usuario
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];

    // 1. Validación básica para evitar guardar campos vacíos
    if (empty($nombre) || empty($correo) || empty($password)) {
        $mensaje = "<div class='alert alert-danger'>Todos los campos son obligatorios.</div>";
    } else {
        // 2. SEGURIDAD: Nunca guardamos contraseñas en texto plano (ej: "123456").
        // password_hash usa el algoritmo de encriptación más fuerte disponible en tu versión de PHP (usualmente bcrypt).
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            // 3. SEGURIDAD CONTRA INYECCIÓN SQL: Usamos sentencias preparadas (prepare).
            // En lugar de meter las variables directo en el SQL, ponemos marcadores (:nombre, :correo).
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, correo, password_hash) VALUES (:nombre, :correo, :password)");
            
            // execute() reemplaza los marcadores con los datos reales de forma segura, escapando caracteres peligrosos.
            $stmt->execute([
                ':nombre' => $nombre,
                ':correo' => $correo,
                ':password' => $password_hash
            ]);
            
            $mensaje = "<div class='alert alert-success'>Registro exitoso. <a href='login.php'>Inicia sesión aquí</a>.</div>";
            
        } catch (PDOException $e) {
            // El código 23000 de SQL significa "Violación de restricción de integridad" (ej. UNIQUE falló).
            // Esto pasa si alguien intenta registrar un correo que ya está en la base de datos.
            if ($e->getCode() == 23000) { 
                $mensaje = "<div class='alert alert-warning'>El correo ya está registrado.</div>";
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al registrar el usuario.</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Encuestas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Registro de Encuestado</h4>
                </div>
                <div class="card-body">
                    <?php echo $mensaje; ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="correo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrarse</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>