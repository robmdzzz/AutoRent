<?php 
require 'config/conexion.php';
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Debes iniciar sesión para reservar un auto.'); window.location.href='login.php';</script>";
    exit;
}

// Validar que vengan los datos por la URL
if (!isset($_GET['id_auto']) || empty($_GET['fecha_inicio']) || empty($_GET['fecha_fin'])) {
    header("Location: catalogo.php");
    exit;
}

$id_auto = $_GET['id_auto'];
$fecha_inicio = $_GET['fecha_inicio'];
$fecha_fin = $_GET['fecha_fin'];

// Recalcular días y total en el servidor (Seguridad)
$fecha1 = new DateTime($fecha_inicio);
$fecha2 = new DateTime($fecha_fin);
$dias = $fecha1->diff($fecha2)->days;
$dias = $dias + 1; // Sumamos 1 día para contar el día de inicio en la renta

// Consultar el auto
$sql = "SELECT * FROM AUTOMOVILES WHERE id_auto = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_auto]);
$auto = $stmt->fetch();

if (!$auto || $dias <= 0) {
    header("Location: catalogo.php");
    exit;
}

$subtotal = $dias * $auto['precio_dia'];
$descuento = 0; // Luego podemos aplicar promociones
$total = $subtotal - $descuento;

// Lógica cuando el usuario hace clic en "Confirmar Reserva"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sqlInsert = "INSERT INTO RESERVACIONES (id_usuario, id_auto, fecha_inicio, fecha_final, numero_dias, subtotal, descuento, total, estado) 
                      VALUES (:id_usuario, :id_auto, :fecha_inicio, :fecha_final, :numero_dias, :subtotal, :descuento, :total, 'confirmada')";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([
            ':id_usuario' => $_SESSION['id_usuario'],
            ':id_auto' => $id_auto,
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_final' => $fecha_fin,
            ':numero_dias' => $dias,
            ':subtotal' => $subtotal,
            ':descuento' => $descuento,
            ':total' => $total
        ]);

        $nuevo_id = $pdo->lastInsertId();

        if (!$nuevo_id || $nuevo_id == 0) {
            die("Error: La reserva se guardó, pero el ID es 0. Revisa la base de datos.");
        }

        // Cambiar el estado del auto a 'rentado'
        $pdo->query("UPDATE AUTOMOVILES SET estado = 'rentado' WHERE id_auto = $id_auto");

        // Redirigir al pop-up
        header("Location: /reserva_exitosa.php?id=" . $nuevo_id);
        exit;

    } catch (PDOException $e) {
        $error = "Hubo un error al procesar la reserva: " . $e->getMessage();
    }
}

require 'includes/header.php'; 
?>

<div class="checkout-container">
    <h1>Confirmar Reservación</h1>

    <?php if(isset($error)) echo "<div class='alert error'>$error</div>"; ?>

    <div class="checkout-grid">
        <!-- Columna Izquierda: Formulario de Pago -->
        <div class="checkout-form">
            <h2>Información de pago</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nombre en tarjeta</label>
                    <input type="text" placeholder="Nombre Apellido" required>
                </div>
                <div class="form-group">
                    <label>Número de tarjeta</label>
                    <input type="text" placeholder="0000 0000 0000 0000" maxlength="19" required>
                </div>
                <div class="fechas-group">
                    <div class="form-group">
                        <label>Fecha de expiración</label>
                        <input type="text" placeholder="MM/AA" maxlength="5" required>
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" placeholder="123" maxlength="4" required>
                    </div>
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" style="width: auto;">
                    <label style="margin: 0;">Guardar tarjeta para futuras reservas</label>
                </div>
        </div>

        <!-- Columna Derecha: Resumen de la Reserva -->
        <div class="checkout-resumen">
            <h2>Resumen de la reserva</h2>
            
            <div class="resumen-auto">
                <div class="placeholder-img" style="width: 80px; height: 60px; border-radius: 4px;">X</div>
                <div>
                    <h3><?php echo htmlspecialchars($auto['marca'] . ' ' . $auto['modelo']); ?></h3>
                    <p><?php echo $auto['categoria_nombre'] ?? 'Auto'; ?> · <?php echo $auto['transmision']; ?></p>
                </div>
            </div>

            <div class="resumen-fechas">
                <div>
                    <span>Inicio</span>
                    <strong><?php echo date('d M Y', strtotime($fecha_inicio)); ?></strong>
                </div>
                <div>
                    <span>Fin</span>
                    <strong><?php echo date('d M Y', strtotime($fecha_fin)); ?></strong>
                </div>
            </div>

            <div class="resumen-totales">
                <div class="linea"><span>Subtotal (<?php echo $dias; ?> días)</span> <span>$<?php echo number_format($subtotal, 2); ?></span></div>
                <div class="linea"><span>Descuento por promoción</span> <span>-$<?php echo number_format($descuento, 2); ?></span></div>
                <div class="linea total"><span>Total a pagar</span> <span>$<?php echo number_format($total, 2); ?></span></div>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; font-size: 16px; margin-top: 20px;">
                Confirmar Reserva
            </button>
            </form> <!-- Cerramos el formulario aquí para que el botón envíe los datos -->
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>