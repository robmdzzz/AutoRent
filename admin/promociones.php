<?php
session_start();
require '../config/conexion.php';

// Verificar que sea administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'administrador') {
    header("Location: /login.php");
    exit;
}

// --- LÓGICA PARA ELIMINAR ---
if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    try {
        $stmt = $pdo->prepare("DELETE FROM PROMOCIONES WHERE id_promocion = :id");
        $stmt->execute([':id' => $id_eliminar]);
        $mensaje_exito = "Promoción eliminada correctamente.";
    } catch (PDOException $e) {
        $mensaje_error = "No se puede eliminar la promoción.";
    }
}

// --- LÓGICA PARA LEER TODAS LAS PROMOCIONES ---
$sql = "SELECT * FROM PROMOCIONES ORDER BY id_promocion DESC";
$promociones = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Promociones - AutoRent Admin</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>

<div class="admin-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-header">
            <div>
                <p style="color: #64748b; font-size: 14px; margin-bottom: 5px;">Dashboard / Promociones</p>
                <h1>Gestión de Promociones</h1>
            </div>
            <!-- Botón para Crear -->
            <a href="promo_form.php" class="btn-primary" style="text-decoration: none; padding: 10px 20px;">+ Agregar Promoción</a>
        </header>

        <?php if(isset($mensaje_exito)): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 6px; margin-bottom: 20px;"><?php echo $mensaje_exito; ?></div>
        <?php endif; ?>
        <?php if(isset($mensaje_error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 20px;"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>

        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descuento</th>
                        <th>Fechas de Vigencia</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($promociones) > 0): ?>
                        <?php foreach($promociones as $promo): ?>
                            <tr>
                                <td>P-<?php echo str_pad($promo['id_promocion'], 3, "0", STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($promo['nombre']); ?></td>
                                <td><strong><?php echo number_format($promo['porcentaje_descuento'], 0); ?>%</strong></td>
                                <td style="font-size: 13px;">
                                    <?php echo date('d/m/Y', strtotime($promo['fecha_inicio'])); ?> al 
                                    <?php echo date('d/m/Y', strtotime($promo['fecha_fin'])); ?>
                                </td>
                                <td>
                                    <!-- Etiqueta de estado (Activa verde, Inactiva gris) -->
                                    <span class="badge badge-<?php echo ($promo['estado'] == 'activa') ? 'disponible' : 'cancelada'; ?>">
                                        <?php echo ucfirst($promo['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="promo_form.php?id=<?php echo $promo['id_promocion']; ?>" class="btn-accion btn-editar">Editar</a>
                                        <a href="promociones.php?eliminar=<?php echo $promo['id_promocion']; ?>" class="btn-accion btn-eliminar" onclick="return confirm('¿Estás seguro de eliminar esta promoción?');">Eliminar</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No hay promociones registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>