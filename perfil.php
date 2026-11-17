<?php 
require 'config/conexion.php';
session_start();

// Si no ha iniciado sesión, redirigir al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$mensaje = '';

// Lógica para actualizar el perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);
    $nueva_contrasena = $_POST['nueva_contrasena'];

    // --- VALIDACIONES PHP ---
    if (strlen($nombre) > 50 || strlen($apellidos) > 50) {
        $mensaje = "<div style='background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 20px;'>El nombre y los apellidos no pueden exceder los 50 caracteres.</div>";
    } elseif (!preg_match('/^[0-9]{10}$/', $telefono)) {
        $mensaje = "<div style='background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 20px;'>El teléfono debe contener exactamente 10 dígitos numéricos.</div>";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "<div style='background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 20px;'>El formato del correo electrónico no es válido.</div>";
    } else {
        try {
            // Verificar que el correo no esté en uso por OTRO usuario
            $stmtCheck = $pdo->prepare("SELECT id_usuario FROM USUARIOS WHERE correo = :correo AND id_usuario != :id");
            $stmtCheck->execute([':correo' => $correo, ':id' => $id_usuario]);
            
            if ($stmtCheck->fetch()) {
                $mensaje = "<div style='background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 20px;'>El correo electrónico ya está registrado por otro usuario.</div>";
            } else {
                // Actualizamos los datos básicos
                $sql = "UPDATE USUARIOS SET nombre=:nom, apellidos=:ape, telefono=:tel, correo=:correo WHERE id_usuario=:id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':nom'=>$nombre, ':ape'=>$apellidos, ':tel'=>$telefono, ':correo'=>$correo, ':id'=>$id_usuario]);

                // Si el usuario escribió una nueva contraseña, la actualizamos
                if (!empty($nueva_contrasena)) {
                    $hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
                    $stmtPass = $pdo->prepare("UPDATE USUARIOS SET contrasena=:pass WHERE id_usuario=:id");
                    $stmtPass->execute([':pass'=>$hash, ':id'=>$id_usuario]);
                }

                // Actualizamos el nombre en la sesión por si lo cambió
                $_SESSION['nombre'] = $nombre;
                $mensaje = "<div style='background: #d1fae5; color: #065f46; padding: 15px; border-radius: 6px; margin-bottom: 20px;'>Perfil actualizado correctamente.</div>";
            }

        } catch (PDOException $e) {
            $mensaje = "<div style='background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 20px;'>Error al actualizar el perfil: " . $e->getMessage() . "</div>";
        }
    }
}

// Consultar los datos actuales del usuario
$stmtUser = $pdo->prepare("SELECT * FROM USUARIOS WHERE id_usuario = :id");
$stmtUser->execute([':id' => $id_usuario]);
$user = $stmtUser->fetch();

require 'includes/header.php'; 
?>

<div class="perfil-container">
    <div style="margin-bottom: 30px;">
        <h1 style="color: #1e293b; font-size: 32px;">Mi Perfil</h1>
        <p style="color: #64748b;">Actualiza tu información de contacto.</p>
    </div>

    <?php echo $mensaje; ?>

    <!-- Tarjeta de Datos Personales -->
    <div class="perfil-card">
        <h2>Datos Personales</h2>
        
        <form method="POST" action="perfil.php" class="perfil-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre</label>
                    <!-- Agregamos maxlength -->
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" maxlength="50" required>
                </div>
                <div class="form-group">
                    <label>Apellidos</label>
                    <!-- Agregamos maxlength -->
                    <input type="text" name="apellidos" value="<?php echo htmlspecialchars($user['apellidos']); ?>" maxlength="50" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="correo" value="<?php echo htmlspecialchars($user['correo']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <!-- Agregamos pattern y maxlength para los 10 dígitos -->
                    <input type="tel" name="telefono" value="<?php echo htmlspecialchars($user['telefono']); ?>" pattern="[0-9]{10}" maxlength="10" title="Ingresa exactamente 10 dígitos numéricos" required>
                </div>
            </div>

            <div class="form-group" style="margin-top: 10px;">
                <label>Nueva Contraseña (dejar en blanco para no cambiar)</label>
                <input type="password" name="nueva_contrasena" placeholder="••••••••">
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 20px; padding: 12px 30px; cursor: pointer; border: none; border-radius: 6px;">Guardar Cambios</button>
        </form>
    </div>
</div>

<?php require 'includes/footer.php'; ?>