<?php
require 'config/conexion.php';
session_start();

if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: /index.php");
    exit;
}

$id_reserva = $_POST['id_reserva'];

// Actualizar el estado a cancelada SOLO si pertenece al usuario logueado
$sql = "UPDATE RESERVACIONES SET estado = 'cancelada' WHERE id_reservacion = :id AND id_usuario = :id_usuario";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_reserva, ':id_usuario' => $_SESSION['id_usuario']]);

// Opcional: Regresar el auto a 'disponible' si la reserva era para hoy o futuro
$sqlAuto = "UPDATE AUTOMOVILES a 
            INNER JOIN RESERVACIONES r ON a.id_auto = r.id_auto 
            SET a.estado = 'disponible' 
            WHERE r.id_reservacion = :id";
$stmtAuto = $pdo->prepare($sqlAuto);
$stmtAuto->execute([':id' => $id_reserva]);

header("Location: /mis_reservaciones.php");
exit;
?>