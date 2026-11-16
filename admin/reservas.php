<?php
session_start();
require '../config/conexion.php';

// Verificar que sea administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'administrador') {
    header("Location: /login.php");
    exit;
}

// --- LÓGICA PARA CAMBIAR EL ESTADO DE UNA RESERVA ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_estado'])) {
    $id_reserva = $_POST['id_reserva'];
    $nuevo_estado = $_POST['nuevo_estado'];

    try {
        // 1. Actualizamos el estado de la reserva
        $stmt = $pdo->prepare("UPDATE RESERVACIONES SET estado = :estado WHERE id_reservacion = :id");
        $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_reserva]);

        // 2. Si se cancela, liberamos el auto para que vuelva a estar disponible
        if ($nuevo_estado == 'cancelada') {
            // Buscamos el id del auto de esta reserva
            $stmtAuto = $pdo->prepare("SELECT id_auto FROM RESERVACIONES WHERE id_reservacion = :id");
            $stmtAuto->execute([':id' => $id_reserva]);
            $reserva = $stmtAuto->fetch();
            
            if ($reserva) {
                $pdo->prepare("UPDATE AUTOMOVILES SET estado = 'disponible' WHERE id_auto = :id_auto")
                    ->execute([':id_auto' => $reserva['id_auto']]);
            }
        }

        $mensaje_exito = "Estado de la reserva actualizado correctamente.";
    } catch (PDOException $e) {
        $mensaje_error = "Error al actualizar: " . $e->getMessage();
    }
}

// --- LÓGICA PARA LEER LAS RESERVAS ---
$sql = "SELECT r.*, u.nombre as cliente, u.apellidos, a.marca, a.modelo 
        FROM RESERVACIONES r 
        INNER JOIN USUARIOS u ON r.id_usuario = u.id_usuario 
        INNER JOIN AUTOMOVILES a ON r.id_auto = a.id_auto 
        ORDER BY r.fecha_reservacion DESC";
$reservas = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Reservaciones - AutoRent Admin</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>

<div class="admin-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-header">
            <div>
                <p style="color: #64748b; font-size: 14px; margin-bottom: 5px;">Dashboard / Reservaciones</p>
                <h1>Gestión de Reservaciones</h1>
            </div>
        </header>

        <!-- Mensajes de éxito o error -->
        <?php if(isset($mensaje_exito)): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                <?php echo $mensaje_exito; ?>
            </div>
        <?php endif; ?>
        <?php if(isset($mensaje_error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID Reserva</th>
                        <th>Cliente</th>
                        <th>Auto</th>
                        <th>Fechas</th>
                        <th>Total</th>
                        <th>Estado Actual</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($reservas) > 0): ?>
                        <?php foreach($reservas as $res): ?>
                            <tr>
                                <td>R-<?php echo str_pad($res['id_reservacion'], 4, "0", STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($res['cliente'] . ' ' . $res['apellidos']); ?></td>
                                <td><?php echo htmlspecialchars($res['marca'] . ' ' . $res['modelo']); ?></td>
                                <td style="font-size: 13px;">
                                    <?php echo date('d/m/Y', strtotime($res['fecha_inicio'])); ?> al 
                                    <?php echo date('d/m/Y', strtotime($res['fecha_final'])); ?>
                                </td>
                                <td>$<?php echo number_format($res['total'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $res['estado']; ?>">
                                        <?php echo ucfirst($res['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Formulario para cambiar el estado rápidamente -->
                                    <form action="reservas.php" method="POST" style="display: flex; gap: 5px; align-items: center;">
                                        <input type="hidden" name="id_reserva" value="<?php echo $res['id_reservacion']; ?>">
                                        
                                        <select name="nuevo_estado" style="padding: 5px; border-radius: 4px; border: 1px solid #cbd5e1; font-size: 13px;">
                                            <option value="confirmada" <?php echo ($res['estado'] == 'confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                                            <option value="pendiente" <?php echo ($res['estado'] == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                            <option value="cancelada" <?php echo ($res['estado'] == 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                                        </select>
                                        
                                        <button type="submit" name="cambiar_estado" class="btn-primary" style="padding: 5px 10px; font-size: 13px; border-radius: 4px; cursor: pointer;">
                                            Actualizar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No hay reservaciones registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>