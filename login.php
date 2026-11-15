<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config/conexion.php';

// Si el usuario ya está logueado, redirigirlo
if (isset($_SESSION['id_usuario'])) {
    header("Location: /index.php");
    exit;
}

$mensaje = '';

// Lógica del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion == 'registro') {
        // Lógica de Registro
        $nombre = trim($_POST['nombre']);
        $apellidos = trim($_POST['apellidos']);
        $correo = trim($_POST['correo']);
        $telefono = trim($_POST['telefono']);
        $contrasena = $_POST['contrasena'];
        
        // --- NUEVAS VALIDACIONES ---
        if (strlen($nombre) > 50 || strlen($apellidos) > 50) {
            $mensaje = "<div class='alert error'>El nombre y los apellidos no pueden exceder los 50 caracteres.</div>";
        } elseif (!preg_match('/^[0-9]{10}$/', $telefono)) {
            $mensaje = "<div class='alert error'>El teléfono debe contener exactamente 10 dígitos numéricos.</div>";
        } else {
            // Encriptar contraseña
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);

            try {
                $sql = "INSERT INTO USUARIOS (nombre, apellidos, correo, contrasena, telefono, rol) 
                        VALUES (:nombre, :apellidos, :correo, :contrasena, :telefono, 'cliente')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':apellidos' => $apellidos,
                    ':correo' => $correo,
                    ':contrasena' => $hash,
                    ':telefono' => $telefono
                ]);
                $mensaje = "<div class='alert success'>¡Cuenta creada con éxito! Ya puedes iniciar sesión.</div>";
            } catch (PDOException $e) {
                $mensaje = "<div class='alert error'>Error al registrar: El correo ya existe o hubo un problema.</div>";
            }
        }
    } elseif ($accion == 'login') {
        // Lógica de Inicio de Sesión
        $correo = trim($_POST['correo']);
        $contrasena = $_POST['contrasena'];

        $sql = "SELECT * FROM USUARIOS WHERE correo = :correo";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
            // Credenciales correctas, crear sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];
            
            // Redirigir según el rol
            if ($usuario['rol'] == 'administrador') {
                header("Location: /admin/dashboard.php"); 
            } else {
                header("Location: /index.php");
            }
            exit;
        } else {
            $mensaje = "<div class='alert error'>Correo o contraseña incorrectos.</div>";
        }
    }
} // <--- Cierra if principal del POST
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso - AutoRent</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f4f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #1e293b; margin-bottom: 20px; }
        .tabs { display: flex; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; }
        .tab { flex: 1; text-align: center; padding: 10px; cursor: pointer; color: #64748b; font-weight: bold; }
        .tab.active { color: #1e3a8a; border-bottom: 2px solid #1e3a8a; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 14px; color: #475569; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #1e3a8a; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        button:hover { background-color: #1e40af; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size: 14px; text-align: center; }
        .success { background-color: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .error { background-color: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
        .hidden { display: none; }
    </style>
</head>
<body>

<div class="container">
    <h2>AutoRent</h2>
    
    <?php echo $mensaje; ?>

    <div class="tabs">
        <div class="tab active" id="tab-login" onclick="cambiarTab('login')">Iniciar Sesión</div>
        <div class="tab" id="tab-registro" onclick="cambiarTab('registro')">Crear Cuenta</div>
    </div>

    <!-- Formulario de Login -->
    <form id="form-login" method="POST" action="login.php">
        <input type="hidden" name="accion" value="login">
        <div class="form-group">
            <label>Correo Electrónico</label>
            <input type="email" name="correo" required>
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="contrasena" required>
        </div>
        <button type="submit">Iniciar Sesión</button>
    </form>

        <!-- Formulario de Registro (Oculto por defecto) -->
    <form id="form-registro" method="POST" action="login.php" class="hidden">
        <input type="hidden" name="accion" value="registro">
        
        <div class="form-group">
            <label>Nombre</label>
            <!-- maxlength limita los caracteres en el navegador -->
            <input type="text" name="nombre" maxlength="50" required>
        </div>
        
        <div class="form-group">
            <label>Apellidos</label>
            <input type="text" name="apellidos" maxlength="50" required>
        </div>
        
        <div class="form-group">
            <label>Correo Electrónico</label>
            <input type="email" name="correo" required>
        </div>
        
        <div class="form-group">
            <label>Teléfono (10 dígitos)</label>
            <!-- pattern exige exactamente 10 números. type="tel" abre el teclado numérico en celulares -->
            <input type="tel" name="telefono" pattern="[0-9]{10}" maxlength="10" title="Ingresa exactamente 10 dígitos numéricos" required>
        </div>
        
        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="contrasena" required>
        </div>
        <button type="submit">Crear Cuenta</button>
    </form>
</div>

<script>
    // Script para cambiar entre pestañas
    function cambiarTab(tab) {
        document.getElementById('form-login').classList.add('hidden');
        document.getElementById('form-registro').classList.add('hidden');
        document.getElementById('tab-login').classList.remove('active');
        document.getElementById('tab-registro').classList.remove('active');

        document.getElementById('form-' + tab).classList.remove('hidden');
        document.getElementById('tab-' + tab).classList.add('active');
    }
</script>

</body>
</html>