<?php 
require 'config/conexion.php';
session_start();

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: /index.php");
    exit;
}

$id_reservacion = $_GET['id'];

// Consultar la reservación recién creada
$sql = "SELECT r.*, a.marca, a.modelo FROM RESERVACIONES r 
        INNER JOIN AUTOMOVILES a ON r.id_auto = a.id_auto 
        WHERE r.id_reservacion = :id AND r.id_usuario = :id_usuario";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_reservacion, ':id_usuario' => $_SESSION['id_usuario']]);
$reserva = $stmt->fetch();

if (!$reserva) {
    die("Error de depuración: No se encontró la reserva con ID: " . $id_reservacion . " para el usuario con ID: " . $_SESSION['id_usuario']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reserva Exitosa - AutoRent</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <style>
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center;
        }
        .modal-content {
            background: white; padding: 40px; border-radius: 8px; text-align: center; max-width: 500px; width: 90%;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .check-icon { font-size: 50px; color: #10b981; margin-bottom: 20px; }
        .modal-content h2 { color: #1e293b; margin-bottom: 10px; }
        .modal-content p { color: #64748b; margin-bottom: 20px; }
        .folio { font-size: 18px; font-weight: bold; color: #1e3a8a; margin-bottom: 30px; display: block; }
        .btn-group { display: flex; flex-direction: column; gap: 10px; }
    </style>
</head>
<body>
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="check-icon">✔️</div>
            <h2>¡Tu reserva ha sido confirmada!</h2>
            <p>Hemos enviado los detalles a tu correo.</p>
            <span class="folio">Folio: #RES-<?php echo str_pad($reserva['id_reservacion'], 4, "0", STR_PAD_LEFT); ?></span>
            
            <div class="btn-group">
                <a href="/mis_reservaciones.php" class="btn-primary" style="text-decoration: none; padding: 12px;">Ir a Mis Reservaciones</a>
                <a href="/index.php" class="btn-outline" style="text-decoration: none; padding: 12px;">Volver al Inicio</a>
            </div>
        </div>
    </div>
</body>
</html>