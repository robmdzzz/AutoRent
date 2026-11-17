<?php
session_start();
require '../config/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'administrador') {
    header("Location: /login.php");
    exit;
}

$id_promo = null;
$nombre = $descripcion = $porcentaje = $fecha_inicio = $fecha_fin = $estado = '';
$titulo = "Agregar Promoción";

if (isset($_GET['id'])) {
    $id_promo = $_GET['id'];
    $titulo = "Editar Promoción";
    
    $stmt = $pdo->prepare("SELECT * FROM PROMOCIONES WHERE id_promocion = :id");
    $stmt->execute([':id' => $id_promo]);
    $promo = $stmt->fetch();
    
    if ($promo) {
        $nombre = $promo['nombre'];
        $descripcion = $promo['descripcion'];
        $porcentaje = $promo['porcentaje_descuento'];
        $fecha_inicio = $promo['fecha_inicio'];
        $fecha_fin = $promo['fecha_fin'];
        $estado = $promo['estado'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $porcentaje = $_POST['porcentaje'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $estado = $_POST['estado'];

    try {
        if ($id_promo) {
            $sql = "UPDATE PROMOCIONES SET nombre=:nom, descripcion=:desc, porcentaje_descuento=:porc, fecha_inicio=:f_in, fecha_fin=:f_fin, estado=:est WHERE id_promocion=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':nom'=>$nombre, ':desc'=>$descripcion, ':porc'=>$porcentaje, ':f_in'=>$fecha_inicio, ':f_fin'=>$fecha_fin, ':est'=>$estado, ':id'=>$id_promo]);
        } else {
            $sql = "INSERT INTO PROMOCIONES (nombre, descripcion, porcentaje_descuento, fecha_inicio, fecha_fin, estado) VALUES (:nom, :desc, :porc, :f_in, :f_fin, :est)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':nom'=>$nombre, ':desc'=>$descripcion, ':porc'=>$porcentaje, ':f_in'=>$fecha_inicio, ':f_fin'=>$fecha_fin, ':est'=>$estado]);
        }
        header("Location: promociones.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error al guardar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo; ?> - AutoRent Admin</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>

<div class="admin-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-header">
            <div>
                <p style="color: #64748b; font-size: 14px; margin-bottom: 5px;">Dashboard / Promociones / <?php echo $titulo; ?></p>
                <h1><?php echo $titulo; ?></h1>
            </div>
            <a href="promociones.php" class="btn-outline" style="text-decoration: none; padding: 10px 20px;">Volver</a>
        </header>

        <?php if(isset($error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 20px;"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="admin-card">
            <form method="POST" class="admin-form">
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label>Nombre de la Promoción</label>
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Descuento (%)</label>
                        <input type="number" name="porcentaje" min="1" max="100" value="<?php echo htmlspecialchars($porcentaje); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="3" required><?php echo htmlspecialchars($descripcion); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha de Inicio</label>
                        <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Fin</label>
                        <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" required>
                            <option value="activa" <?php echo ($estado == 'activa') ? 'selected' : ''; ?>>Activa</option>
                            <option value="inactiva" <?php echo ($estado == 'inactiva') ? 'selected' : ''; ?>>Inactiva</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn-primary" style="padding: 12px 30px; cursor: pointer;">Guardar Promoción</button>
                    <a href="promociones.php" class="btn-outline" style="padding: 12px 30px; text-decoration: none;">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>

</body>
</html>