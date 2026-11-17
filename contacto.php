<?php 
require 'config/conexion.php';
require 'includes/header.php'; 

$mensaje_exito = '';

// Lógica para guardar el mensaje
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $asunto = trim($_POST['asunto']);
    $mensaje = trim($_POST['mensaje']);

    if (!empty($nombre) && !empty($correo) && !empty($mensaje)) {
        try {
            $sql = "INSERT INTO MENSAJES (nombre, correo, asunto, mensaje) VALUES (:nom, :correo, :asunto, :mensaje)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom' => $nombre,
                ':correo' => $correo,
                ':asunto' => $asunto,
                ':mensaje' => $mensaje
            ]);
            $mensaje_exito = "¡Tu mensaje ha sido enviado! Te contactaremos pronto.";
        } catch (PDOException $e) {
            $error = "Hubo un error al enviar el mensaje.";
        }
    }
}
?>

<div class="contacto-container">
    <div style="text-align: center; margin-bottom: 40px;">
        <p style="color: #64748b; font-weight: bold; letter-spacing: 1px;">CONTACTO</p>
        <h1 style="font-size: 36px; color: #1e293b; margin-top: 10px;">Contáctanos</h1>
        <p style="color: #64748b; margin-top: 10px;">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
    </div>

    <div class="contacto-grid">
        <!-- Formulario -->
        <div class="contacto-form-card">
            <h2>Envíanos un mensaje</h2>
            
            <?php if($mensaje_exito): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                    <?php echo $mensaje_exito; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="contacto.php">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="correo" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Asunto</label>
                    <input type="text" name="asunto" required>
                </div>
                <div class="form-group">
                    <label>Mensaje</label>
                    <textarea name="mensaje" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn-primary" style="padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer;">Enviar Mensaje</button>
            </form>
        </div>

        <!-- Información de Contacto -->
        <div class="contacto-info-card">
            <h2>Información de Contacto</h2>
            
            <div class="info-item">
                <span class="info-icon">&#128242;</span>
                <div>
                    <h4>TELÉFONO</h4>
                    <p>+52 123 456 7890</p>
                </div>
            </div>
            <div class="info-item">
                <span class="info-icon">&#128233;</span>
                <div>
                    <h4>CORREO</h4>
                    <p>contacto@autorent.com</p>
                </div>
            </div>
            <div class="info-item">
                <span class="info-icon">&#128205;</span>
                <div>
                    <h4>DIRECCIÓN</h4>
                    <p>San Nicolás de los Garza, N.L., México</p>
                </div>
            </div>
            <div class="info-item">
                <span class="info-icon">&#128338;</span>
                <div>
                    <h4>HORARIO</h4>
                    <p>Lunes a Viernes de 9:00 a 18:00 hrs</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>