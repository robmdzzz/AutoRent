<?php
session_start();
require '../config/conexion.php';

// Verificar que sea administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'administrador') {
    header("Location: /login.php");
    exit;
}

// Variables por defecto (para modo Crear)
$id_auto = null;
$marca = $modelo = $año = $precio_dia = $pasajeros = $transmision = $descripcion = $imagen = '';
$id_categoria = $estado = '';
$titulo = "Agregar Automóvil";

// Si viene un ID por la URL, es porque vamos a EDITAR
if (isset($_GET['id'])) {
    $id_auto = $_GET['id'];
    $titulo = "Editar Automóvil";
    
    $stmt = $pdo->prepare("SELECT * FROM AUTOMOVILES WHERE id_auto = :id");
    $stmt->execute([':id' => $id_auto]);
    $auto = $stmt->fetch();
    
    if ($auto) {
        $marca = $auto['marca'];
        $modelo = $auto['modelo'];
        $año = $auto['año'];
        $precio_dia = $auto['precio_dia'];
        $pasajeros = $auto['pasajeros'];
        $transmision = $auto['transmision'];
        $descripcion = $auto['descripcion'];
        $imagen = $auto['imagen'];
        $id_categoria = $auto['id_categoria'];
        $estado = $auto['estado'];
    }
}

// Obtener categorías para el menú desplegable
$categorias = $pdo->query("SELECT * FROM CATEGORIAS")->fetchAll();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $anio = $_POST['año']; // Recibimos el dato del formulario
    $precio_dia = $_POST['precio_dia'];
    $pasajeros = $_POST['pasajeros'];
    $transmision = $_POST['transmision'];
    $descripcion = $_POST['descripcion'];
    $id_categoria = $_POST['id_categoria'];
    $estado = $_POST['estado'];
    
    // Manejo de la imagen
    $nombre_imagen = $imagen; 
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $nombre_imagen = time() . '_' . $_FILES['imagen']['name'];
        $ruta_destino = '../assets/img/' . $nombre_imagen;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino);
    }

    try {
        if ($id_auto) {
            // UPDATE (Actualizar) - Usamos :anio en lugar de :año
            $sql = "UPDATE AUTOMOVILES SET marca=:marca, modelo=:modelo, año=:anio, precio_dia=:precio, pasajeros=:pasajeros, transmision=:transmision, descripcion=:desc, imagen=:imagen, id_categoria=:cat, estado=:estado WHERE id_auto=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':marca' => $marca, ':modelo' => $modelo, ':anio' => $anio, ':precio' => $precio_dia,
                ':pasajeros' => $pasajeros, ':transmision' => $transmision, ':desc' => $descripcion,
                ':imagen' => $nombre_imagen, ':cat' => $id_categoria, ':estado' => $estado, ':id' => $id_auto
            ]);
        } else {
            // INSERT (Crear) - Usamos :anio en lugar de :año
            $sql = "INSERT INTO AUTOMOVILES (marca, modelo, año, precio_dia, pasajeros, transmision, descripcion, imagen, id_categoria, estado) 
                    VALUES (:marca, :modelo, :anio, :precio, :pasajeros, :transmision, :desc, :imagen, :cat, :estado)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':marca' => $marca, ':modelo' => $modelo, ':anio' => $anio, ':precio' => $precio_dia,
                ':pasajeros' => $pasajeros, ':transmision' => $transmision, ':desc' => $descripcion,
                ':imagen' => $nombre_imagen, ':cat' => $id_categoria, ':estado' => $estado
            ]);
        }
        
        // Redirigir a la tabla con mensaje de éxito
        header("Location: autos.php");
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
                <p style="color: #64748b; font-size: 14px; margin-bottom: 5px;">Dashboard / Automóviles / <?php echo $titulo; ?></p>
                <h1><?php echo $titulo; ?></h1>
            </div>
            <a href="autos.php" class="btn-outline" style="text-decoration: none; padding: 10px 20px;">Volver al Catálogo</a>
        </header>

        <?php if(isset($error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <!-- IMPORTANTE: enctype="multipart/form-data" permite subir archivos (la imagen) -->
            <form method="POST" enctype="multipart/form-data" class="admin-form">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Marca</label>
                        <input type="text" name="marca" value="<?php echo htmlspecialchars($marca); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Modelo</label>
                        <input type="text" name="modelo" value="<?php echo htmlspecialchars($modelo); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Año</label>
                        <input type="number" name="año" value="<?php echo htmlspecialchars($año); ?>" min="2000" max="2026" required>
                    </div>
                    <div class="form-group">
                        <label>Precio por día ($)</label>
                        <input type="number" step="0.01" name="precio_dia" value="<?php echo htmlspecialchars($precio_dia); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Pasajeros</label>
                        <input type="number" name="pasajeros" value="<?php echo htmlspecialchars($pasajeros); ?>" min="1" max="10" required>
                    </div>
                    <div class="form-group">
                        <label>Transmisión</label>
                        <select name="transmision" required>
                            <option value="automatica" <?php echo ($transmision == 'automatica') ? 'selected' : ''; ?>>Automática</option>
                            <option value="manual" <?php echo ($transmision == 'manual') ? 'selected' : ''; ?>>Manual</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Categoría</label>
                        <select name="id_categoria" required>
                            <option value="">Selecciona una categoría</option>
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($id_categoria == $cat['id_categoria']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" required>
                            <option value="disponible" <?php echo ($estado == 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                            <option value="rentado" <?php echo ($estado == 'rentado') ? 'selected' : ''; ?>>Rentado</option>
                            <option value="mantenimiento" <?php echo ($estado == 'mantenimiento') ? 'selected' : ''; ?>>Mantenimiento</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="3" required><?php echo htmlspecialchars($descripcion); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Imagen del vehículo</label>
                    <input type="file" name="imagen" accept="image/*" <?php echo (!$id_auto) ? 'required' : ''; ?>>
                    <?php if($imagen): ?>
                        <p style="font-size: 12px; color: #64748b; margin-top: 5px;">Imagen actual: <?php echo $imagen; ?></p>
                    <?php endif; ?>
                </div>

                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn-primary" style="padding: 12px 30px;">Guardar Automóvil</button>
                    <a href="autos.php" class="btn-outline" style="padding: 12px 30px; text-decoration: none;">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>

</body>
</html>