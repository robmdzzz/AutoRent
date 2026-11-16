<?php
// ob_start() evita problemas con los encabezados (headers) y la sesión
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config/conexion.php';

// Verificar que sea administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'administrador') {
    header("Location: /login.php");
    exit;
}

// --- LÓGICA PARA CAMBIAR EL ESTADO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_estado'])) {
    $usuario_a_modificar = $_POST['id_usuario_form'];
    $nuevo_estado = $_POST['nuevo_estado'];

    // Seguridad: Evitar que el admin se suspenda a sí mismo
    if ($usuario_a_modificar == $_SESSION['id_usuario']) {
        $mensaje_error = "No puedes suspender tu propia cuenta de administrador.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE USUARIOS SET estado = :estado WHERE id_usuario = :id");
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $usuario_a_modificar]);
            $mensaje_exito = "Estado del usuario actualizado correctamente.";
        } catch (PDOException $e) {
            $mensaje_error = "Error al actualizar: " . $e->getMessage();
        }
    }
}

// --- LÓGICA PARA LEER LOS USUARIOS ---
$sql = "SELECT * FROM USUARIOS ORDER BY id_usuario ASC";
$usuarios = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - AutoRent Admin</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>

<div class="admin-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-header">
            <div>
                <p style="color: #64748b; font-size: 14px; margin-bottom: 5px;">Dashboard / Usuarios</p>
                <h1>Gestión de Usuarios</h1>
            </div>
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
                        <th>Nombre Completo</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($usuarios as $user): ?>
                        <tr>
                            <td>U-<?php echo str_pad($user['id_usuario'], 3, "0", STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellidos']); ?></td>
                            <td><?php echo htmlspecialchars($user['correo']); ?></td>
                            <td>
                                <span class="badge" style="background: #e2e8f0; color: #1e293b;">
                                    <?php echo ucfirst($user['rol']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo ($user['estado'] == 'activo') ? 'disponible' : 'cancelada'; ?>">
                                    <?php echo ucfirst($user['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($user['id_usuario'] == $_SESSION['id_usuario']): ?>
                                    <span style="font-size: 12px; color: #94a3b8;">Tu cuenta</span>
                                <?php else: ?>
                                    <form action="usuarios.php" method="POST" style="display: flex; gap: 5px;">
                                        <input type="hidden" name="id_usuario_form" value="<?php echo $user['id_usuario']; ?>">
                                        
                                        <select name="nuevo_estado" style="padding: 4px; border-radius: 4px; border: 1px solid #cbd5e1; font-size: 13px;">
                                            <option value="activo" <?php echo ($user['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                                            <option value="suspendido" <?php echo ($user['estado'] == 'suspendido') ? 'selected' : ''; ?>>Suspendido</option>
                                        </select>
                                        
                                        <button type="submit" name="cambiar_estado" class="btn-primary" style="padding: 4px 10px; font-size: 12px; cursor: pointer;">
                                            Aplicar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
<?php ob_end_flush(); ?>