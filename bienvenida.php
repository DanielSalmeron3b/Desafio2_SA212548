<?php
// ENCIENDE LA VISUALIZACIÓN DE ERRORES (Bórralo cuando termines de arreglarlo)
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

session_start();
require 'db.php';

// CONTROL DE ACCESO (Protección de ruta)

// Si no existe la variable de sesión 'usuario_id', significa que el visitante
// no pasó por el login. Lo pateamos de vuelta al login.
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Rescatamos los datos de la sesión para usarlos más fácil en el HTML
$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['nombre_completo'];
$mensaje = '';

// ==========================================
// 2. PROCESAMIENTO DEL FORMULARIO DE VOTO
// ==========================================
// Verificamos si se envió el formulario y si se seleccionó una opción (radio button)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['opcion_voto'])) {
    $opcion = $_POST['opcion_voto'];
    
    try {
        // Intentamos guardar el voto. Recuerda que 'usuario_id' es UNIQUE en la tabla votos.
        $stmt = $pdo->prepare("INSERT INTO votos (usuario_id, opcion) VALUES (:usuario_id, :opcion)");
        $stmt->execute([':usuario_id' => $usuario_id, ':opcion' => $opcion]);
        
        $mensaje = "<div class='alert alert-success'>¡Gracias por tu voto! Tu opinión ha sido registrada.</div>";
        
    } catch (PDOException $e) {
        // Si el usuario intenta votar de nuevo, la base de datos rechazará el INSERT por el UNIQUE,
        // lanzando el error 23000. Lo capturamos y le avisamos amablemente.
        if ($e->getCode() == 23000) {
            $mensaje = "<div class='alert alert-warning'>Ya has participado en esta encuesta. No puedes votar dos veces.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al registrar el voto.</div>";
        }
    }
}

// ==========================================
// 3. CÁLCULO DE ESTADÍSTICAS EN TIEMPO REAL
// ==========================================
// Hacemos una consulta agrupada (GROUP BY). Esto cuenta cuántas filas hay de cada opción.
// Nos devolverá algo como: Excelente -> 5, Bueno -> 3, etc.
$stmt = $pdo->query("SELECT opcion, COUNT(*) as cantidad FROM votos GROUP BY opcion");
$resultados = $stmt->fetchAll(); // fetchAll() trae todos los resultados en un array

// Calculamos el total de votos sumando las cantidades para poder sacar los porcentajes después en el HTML.
$total_votos = 0;
foreach ($resultados as $row) {
    $total_votos += $row->cantidad;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel - Encuestas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">Sistema de Encuestas</a>
        <div class="d-flex text-white align-items-center">
            <span class="me-3">Hola, <strong><?= htmlspecialchars($nombre_usuario) ?></strong></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container">
    <?= $mensaje ?>
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Encuesta de Satisfacción</h5>
                </div>
                <div class="card-body">
                    <p>¿Qué tan satisfecho estás con nuestro servicio de plataforma web?</p>
                    <form method="POST" action="">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="opcion_voto" value="Excelente" id="excelente" required>
                            <label class="form-check-label" for="excelente">Excelente</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="opcion_voto" value="Bueno" id="bueno">
                            <label class="form-check-label" for="bueno">Bueno</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="opcion_voto" value="Regular" id="regular">
                            <label class="form-check-label" for="regular">Regular</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="opcion_voto" value="Malo" id="malo">
                            <label class="form-check-label" for="malo">Malo</label>
                        </div>
                        <button type="submit" class="btn btn-success">Enviar Voto</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Resultados en Tiempo Real</h5>
                </div>
                <div class="card-body">
                    <p>Total de votos registrados: <strong><?= $total_votos ?></strong></p>
                    
                    <?php if ($total_votos > 0): ?>
                        <?php foreach ($resultados as $row): 
                            $porcentaje = round(($row->cantidad / $total_votos) * 100);
                        ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span><?= htmlspecialchars($row->opcion) ?></span>
                                    <span><?= $row->cantidad ?> votos (<?= $porcentaje ?>%)</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: <?= $porcentaje ?>%;" aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Aún no hay votos registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>