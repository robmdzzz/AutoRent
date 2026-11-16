<?php
session_start();
require '../config/conexion.php';

// Verificar que sea administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'administrador') {
    header("Location: /login.php");
    exit;
}

// --- LÓGICA PARA ELIMINAR UN AUTO ---
if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    try {
        $stmt = $pdo->prepare("DELETE FROM AUTOMOVILES WHERE id_auto = :id");
        $stmt->execute([':id' => $id_eliminar]);
        $mensaje_exito = "Vehículo eliminado correctamente.";
    } catch (PDOException $e) {
        $mensaje_error = "No se puede eliminar el vehículo porque tiene reservaciones asociadas.";
    }
}

// --- LÓGICA PARA LEER LOS AUTOS ---
// Hacemos un JOIN para traer el nombre de la categoría en lugar del ID
$sql = "SELECT a.*, c.nombre as categoria_nombre 
        FROM AUTOMOVILES a 
        LEFT JOIN CATEGORIAS c ON a.id_categoria = c.id_categoria 
        ORDER BY a.id_auto DESC";
$autos = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Automóviles - AutoRent Admin</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>

<div class="admin-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-header">
            <div>
                <p style="color: #64748b; font-size: 14px; margin-bottom: 5px;">Dashboard / Automóviles</p>
                <h1>Gestión de Automóviles</h1>
            </div>
            <!-- Botón para Crear -->
            <a href="auto_form.php" class="btn-primary" style="text-decoration: none; padding: 10px 20px;">+ Agregar Automóvil</a>
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
                        <th>ID</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Año</th>
                        <th>Categoría</th>
                        <th>Precio/día</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($autos) > 0): ?>
                        <?php foreach($autos as $auto): ?>
                            <tr>
                                <td>A-<?php echo str_pad($auto['id_auto'], 3, "0", STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($auto['marca']); ?></td>
                                <td><?php echo htmlspecialchars($auto['modelo']); ?></td>
                                <td><?php echo htmlspecialchars($auto['año']); ?></td>
                                <td><?php echo htmlspecialchars($auto['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                                <td>$<?php echo number_format($auto['precio_dia'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $auto['estado']; ?>">
                                        <?php echo ucfirst($auto['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <!-- Botón Editar -->
                                        <a href="auto_form.php?id=<?php echo $auto['id_auto']; ?>" class="btn-accion btn-editar">Editar</a>
                                        
                                        <!-- Botón Eliminar (con confirmación) -->
                                        <a href="autos.php?eliminar=<?php echo $auto['id_auto']; ?>" class="btn-accion btn-eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este vehículo?');">Eliminar</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No hay vehículos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>