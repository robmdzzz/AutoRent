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
    header("Location: /index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reserva Exitosa - AutoRent</title>
    <style>
        /* Forzamos la fuente Arial y el fondo gris claro en toda la página */
        body {
            font-family: Arial, sans-serif !important; 
            background-color: #f8fafc !important; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0;
        }
        
        .modal-card {
            background: white; 
            padding: 50px 40px; 
            border-radius: 12px; 
            text-align: center; 
            max-width: 450px; 
            width: 90%;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }

        .check-icon {
            width: 80px;
            height: 80px;
            background-color: #d1fae5; 
            color: #10b981; 
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 40px;
            margin: 0 auto 20px auto;
            font-family: Arial, sans-serif;
        }

        .modal-card h2 {
            color: #1e293b !important;
            font-size: 24px;
            margin-bottom: 10px;
            font-family: Arial, sans-serif !important;
        }

        .modal-card p {
            color: #64748b !important;
            font-size: 15px;
            margin-bottom: 25px;
            font-family: Arial, sans-serif !important;
        }

        .folio-box {
            background-color: #f1f5f9;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            color: #1e3a8a !important;
            margin-bottom: 30px;
            display: block;
            border: 1px dashed #cbd5e1;
            font-family: Arial, sans-serif !important;
        }

        /* Forzamos a que los enlaces se vean como botones sólidos */
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .btn-group a {
            text-decoration: none !important;
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 14px;
            font-size: 16px;
            border-radius: 6px;
            font-weight: bold;
            font-family: Arial, sans-serif !important;
            transition: background 0.2s;
        }

        /* Botón Azul */
        .btn-azul {
            background-color: #1e3a8a !important;
            color: #ffffff !important;
            border: 1px solid #1e3a8a !important;
        }
        .btn-azul:hover { background-color: #1e40af !important; }

        /* Botón Blanco con borde */
        .btn-blanco {
            background-color: #ffffff !important;
            color: #1e293b !important;
            border: 1px solid #cbd5e1 !important;
        }
        .btn-blanco:hover { background-color: #f8fafc !important; }
    </style>
</head>
<body>
    <div class="modal-card">
        <div class="check-icon">✔</div>
        <h2>¡Tu reserva ha sido confirmada!</h2>
        <p>Hemos enviado los detalles de tu renta a tu correo electrónico.</p>
        
        <span class="folio-box">
            Folio: #RES-<?php echo str_pad($reserva['id_reservacion'], 4, "0", STR_PAD_LEFT); ?>
        </span>
        
        <div class="btn-group">
            <!-- Usamos las nuevas clases que forzamos arriba -->
            <a href="/mis_reservaciones.php" class="btn-azul">Ir a Mis Reservaciones</a>
            <a href="/index.php" class="btn-blanco">Volver al Inicio</a>
        </div>
    </div>
</body>
</html>