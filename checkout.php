<?php 
require 'config/conexion.php';
session_start();
date_default_timezone_set('America/Mexico_City');

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

$id_auto      = (int)$_GET['id_auto'];
$fecha_inicio = (string)$_GET['fecha_inicio'];
$fecha_fin    = (string)$_GET['fecha_fin'];
$hoy          = date('Y-m-d');

// Validar formato y orden de las fechas (nunca confiar solo en el calendario del navegador)
$fecha1 = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha_inicio);
$fecha2 = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha_fin);
if (!$fecha1 || !$fecha2
    || $fecha1->format('Y-m-d') !== $fecha_inicio
    || $fecha2->format('Y-m-d') !== $fecha_fin
    || $fecha_inicio < $hoy
    || $fecha_fin < $fecha_inicio) {
    header("Location: catalogo.php");
    exit;
}

// Recalcular días en el servidor
$dias = $fecha1->diff($fecha2)->days + 1; 

// Consultar el auto (con su categoría)
$sql = "SELECT a.*, c.nombre AS categoria_nombre 
        FROM AUTOMOVILES a
        INNER JOIN CATEGORIAS c ON a.id_categoria = c.id_categoria
        WHERE a.id_auto = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_auto]);
$auto = $stmt->fetch();

if (!$auto || $auto['estado'] === 'mantenimiento' || $dias <= 0) {
    header("Location: catalogo.php");
    exit;
}

/**
 * ¿El auto ya tiene una reserva confirmada o pendiente que se traslape con estas fechas?
 */
function auto_ocupado(PDO $pdo, $id_auto, $inicio, $fin) {
    $st = $pdo->prepare("SELECT COUNT(*) FROM RESERVACIONES
                         WHERE id_auto = :id
                         AND estado IN ('confirmada', 'pendiente')
                         AND fecha_inicio <= :fin
                         AND fecha_final  >= :inicio");
    $st->execute([':id' => $id_auto, ':fin' => $fin, ':inicio' => $inicio]);
    return $st->fetchColumn() > 0;
}

// Si las fechas ya no están libres, regresamos al detalle con un aviso
if (auto_ocupado($pdo, $id_auto, $fecha_inicio, $fecha_fin)) {
    header("Location: detalle.php?id=" . $id_auto . "&error=ocupado");
    exit;
}

$subtotal = $dias * $auto['precio_dia'];

// --- LÓGICA DE PROMOCIONES ---
// Buscamos la promoción activa con el mayor descuento
$sql_promo = "SELECT id_promocion, nombre, porcentaje_descuento 
              FROM PROMOCIONES 
              WHERE estado = 'activa' 
              AND fecha_inicio <= CURDATE() 
              AND fecha_fin >= CURDATE() 
              ORDER BY porcentaje_descuento DESC LIMIT 1";
$stmt_promo = $pdo->query($sql_promo);
$promocion_aplicada = $stmt_promo->fetch();

$descuento = 0;
$id_promocion_guardar = null;
$nombre_promocion = "Ninguna";

if ($promocion_aplicada) {
    $descuento = $subtotal * ($promocion_aplicada['porcentaje_descuento'] / 100);
    $id_promocion_guardar = $promocion_aplicada['id_promocion'];
    $nombre_promocion = $promocion_aplicada['nombre'] . " (" . $promocion_aplicada['porcentaje_descuento'] . "%)";
}

$total = $subtotal - $descuento;

// Lógica cuando el usuario hace clic en "Confirmar Reserva"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // Bloqueamos el registro del auto: si dos clientes reservan al mismo tiempo,
        // el segundo espera a que termine el primero y entonces ve la reserva ya guardada.
        $pdo->prepare("SELECT id_auto FROM AUTOMOVILES WHERE id_auto = :id FOR UPDATE")
            ->execute([':id' => $id_auto]);

        // Verificamos de nuevo, ya con el auto bloqueado
        if (auto_ocupado($pdo, $id_auto, $fecha_inicio, $fecha_fin)) {
            $pdo->rollBack();
            header("Location: detalle.php?id=" . $id_auto . "&error=ocupado");
            exit;
        }

        $sqlInsert = "INSERT INTO RESERVACIONES (id_usuario, id_auto, id_promocion, fecha_inicio, fecha_final, numero_dias, subtotal, descuento, total, estado) 
                      VALUES (:id_usuario, :id_auto, :id_promocion, :fecha_inicio, :fecha_final, :numero_dias, :subtotal, :descuento, :total, 'confirmada')";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([
            ':id_usuario' => $_SESSION['id_usuario'],
            ':id_auto' => $id_auto,
            ':id_promocion' => $id_promocion_guardar,
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_final' => $fecha_fin,
            ':numero_dias' => $dias,
            ':subtotal' => $subtotal,
            ':descuento' => $descuento,
            ':total' => $total
        ]);

        $nuevo_id = $pdo->lastInsertId();

        if (!$nuevo_id || $nuevo_id == 0) {
            throw new PDOException("La reserva se guardó, pero el ID es 0. Revisa la base de datos.");
        }

        // El auto solo pasa a 'rentado' si la renta empieza hoy. Una reserva futura NO
        // lo marca como rentado: su disponibilidad se controla por fechas.
        if ($fecha_inicio <= $hoy) {
            $pdo->prepare("UPDATE AUTOMOVILES SET estado = 'rentado' WHERE id_auto = :id")
                ->execute([':id' => $id_auto]);
        }

        $pdo->commit();

        // Redirigir al pop-up
        header("Location: /reserva_exitosa.php?id=" . $nuevo_id);
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        $error = "Hubo un error al procesar la reserva: " . $e->getMessage();
    }
}

require 'includes/header.php'; 
?>

<div class="checkout-container">
    <h1>Confirmar Reservación</h1>

    <?php if(isset($error)) echo "<div class='alert error'>" . htmlspecialchars($error) . "</div>"; ?>

    <div class="checkout-grid">
        <!-- Formulario de Pago -->
        <div class="checkout-form">
            <h2>Información de pago</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nombre en tarjeta</label>
                    <input type="text" placeholder="Nombre Apellido" required>
                </div>
                <div class="form-group">
                    <label>Número de tarjeta</label>
                    <input type="text" id="card_number" placeholder="0000 0000 0000 0000" maxlength="19" required>
                </div>
                <div class="fechas-group">
                    <div class="form-group">
                        <label>Fecha de expiración</label>
                        <input type="text" id="exp_date" placeholder="MM/AA" maxlength="5" required>
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

        <!-- Resumen de la Reserva -->
        <div class="checkout-resumen">
            <h2>Resumen de la reserva</h2>
            
            <div class="resumen-auto">
                <div class="placeholder-img" style="width: 80px; height: 60px; border-radius: 4px;">X</div>
                <div>
                    <h3><?php echo htmlspecialchars($auto['marca'] . ' ' . $auto['modelo']); ?></h3>
                    <p><?php echo htmlspecialchars($auto['categoria_nombre']); ?> · <?php echo htmlspecialchars($auto['transmision']); ?></p>
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
                <!-- Mostramos el nombre de la promoción aplicada -->
                <div class="linea"><span>Descuento (<?php echo htmlspecialchars($nombre_promocion); ?>)</span> <span style="color: #10b981;">-$<?php echo number_format($descuento, 2); ?></span></div>
                <div class="linea total"><span>Total a pagar</span> <span>$<?php echo number_format($total, 2); ?></span></div>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; font-size: 16px; margin-top: 20px;">
                Confirmar Reserva
            </button>
            </form>
        </div>
    </div>
</div>

<!-- Script para formatear tarjeta y fecha -->
<script>
    // 1. Formatear el número de tarjeta (Agrega espacios cada 4 dígitos)
    const inputTarjeta = document.getElementById('card_number');
    inputTarjeta.addEventListener('input', function(e) {
        let valor = this.value.replace(/\D/g, '');
        valor = valor.replace(/(\d{4})(?=\d)/g, '$1 ');
        this.value = valor;
    });

    // 2. Formatear la fecha de expiración (Agrega la diagonal automáticamente)
    const inputFecha = document.getElementById('exp_date');
    inputFecha.addEventListener('input', function(e) {
        let valor = this.value.replace(/\D/g, '');
        if (valor.length > 2) {
            valor = valor.substring(0, 2) + '/' + valor.substring(2, 4);
        }
        this.value = valor;
    });
</script>

<?php require 'includes/footer.php'; ?>