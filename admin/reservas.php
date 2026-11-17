<?php
session_start();
require '../config/conexion.php';

date_default_timezone_set('America/Mexico_City');

// Verificar que sea administrador
if (!isset($_SESSION['id_usuario']) || ($_SESSION['rol'] ?? '') !== 'administrador') {
    header("Location: /login.php");
    exit;
}

$ESTADOS    = ['confirmada', 'pendiente', 'cancelada'];
$POR_PAGINA = 10;

// ---------------------------------------------------------------
// Utilidades
// ---------------------------------------------------------------
function get_texto($clave) {
    return (isset($_GET[$clave]) && is_string($_GET[$clave])) ? trim($_GET[$clave]) : '';
}

function fecha_valida($v) {
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $v);
    return ($d && $d->format('Y-m-d') === $v) ? $v : '';
}

// Arma la URL de esta página conservando los filtros activos
function url_reservas(array $filtros, $pagina = 1) {
    if ($pagina > 1) { $filtros['pagina'] = $pagina; }
    $qs = http_build_query($filtros);
    return 'reservas.php' . ($qs ? '?' . $qs : '');
}

/**
 * Deja el campo AUTOMOVILES.estado coherente con las reservaciones:
 *  - 'mantenimiento' nunca se toca (lo decide el administrador).
 *  - 'rentado'    si el auto tiene una reserva confirmada/pendiente EN CURSO hoy.
 *  - 'disponible' en cualquier otro caso (incluye cuando solo tiene reservas futuras;
 *    la disponibilidad por fechas se valida contra las reservaciones).
 */
function sincronizar_estado_auto(PDO $pdo, $id_auto, $hoy) {
    $st = $pdo->prepare("SELECT estado FROM AUTOMOVILES WHERE id_auto = :id");
    $st->execute([':id' => $id_auto]);
    $auto = $st->fetch(PDO::FETCH_ASSOC);
    if (!$auto || $auto['estado'] === 'mantenimiento') { return; }

    $st = $pdo->prepare("SELECT COUNT(*) FROM RESERVACIONES
                         WHERE id_auto = :id
                         AND estado IN ('confirmada', 'pendiente')
                         AND fecha_inicio <= :hoy1
                         AND fecha_final  >= :hoy2");
    $st->execute([':id' => $id_auto, ':hoy1' => $hoy, ':hoy2' => $hoy]);
    $nuevo = ($st->fetchColumn() > 0) ? 'rentado' : 'disponible';

    $pdo->prepare("UPDATE AUTOMOVILES SET estado = :estado WHERE id_auto = :id")
        ->execute([':estado' => $nuevo, ':id' => $id_auto]);
}

// ---------------------------------------------------------------
// Filtros (vienen por GET) y página
// ---------------------------------------------------------------
$busqueda  = get_texto('q');
$f_estado  = get_texto('estado');
if (!in_array($f_estado, $ESTADOS, true)) { $f_estado = ''; }
$f_desde   = fecha_valida(get_texto('desde'));
$f_hasta   = fecha_valida(get_texto('hasta'));
if ($f_desde !== '' && $f_hasta !== '' && $f_desde > $f_hasta) {
    [$f_desde, $f_hasta] = [$f_hasta, $f_desde];    // fechas puestas al revés
}
$pagina = max(1, (int)get_texto('pagina'));

$filtros = array_filter([
    'q'      => $busqueda,
    'estado' => $f_estado,
    'desde'  => $f_desde,
    'hasta'  => $f_hasta,
], function ($v) { return $v !== ''; });

// ---------------------------------------------------------------
// CAMBIAR EL ESTADO DE UNA RESERVA (Update / borrado lógico)
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $id_reserva   = (int)($_POST['id_reserva'] ?? 0);
    $nuevo_estado = $_POST['nuevo_estado'] ?? '';
    $hoy          = date('Y-m-d');

    try {
        if (!in_array($nuevo_estado, $ESTADOS, true)) {
            throw new RuntimeException("Estado no válido.");
        }

        $pdo->beginTransaction();

        $st = $pdo->prepare("SELECT id_auto, estado, fecha_inicio, fecha_final
                             FROM RESERVACIONES WHERE id_reservacion = :id FOR UPDATE");
        $st->execute([':id' => $id_reserva]);
        $reserva = $st->fetch(PDO::FETCH_ASSOC);

        if (!$reserva) {
            throw new RuntimeException("La reservación no existe.");
        }

        if ($reserva['estado'] === $nuevo_estado) {
            $pdo->rollBack();
            $_SESSION['flash'] = ['tipo' => 'exito', 'texto' => "La reservación ya estaba en estado \"$nuevo_estado\"."];
        } else {
            // Reactivar una reserva cancelada: el auto no debe estar ya reservado en esas fechas
            if ($reserva['estado'] === 'cancelada' && $nuevo_estado !== 'cancelada') {
                $st = $pdo->prepare("SELECT COUNT(*) FROM RESERVACIONES
                                     WHERE id_auto = :auto
                                     AND id_reservacion <> :id
                                     AND estado IN ('confirmada', 'pendiente')
                                     AND fecha_inicio <= :fin
                                     AND fecha_final  >= :ini");
                $st->execute([
                    ':auto' => $reserva['id_auto'],
                    ':id'   => $id_reserva,
                    ':fin'  => $reserva['fecha_final'],
                    ':ini'  => $reserva['fecha_inicio'],
                ]);
                if ($st->fetchColumn() > 0) {
                    throw new RuntimeException("No se puede reactivar: el auto ya tiene otra reserva vigente en esas fechas.");
                }
            }

            $pdo->prepare("UPDATE RESERVACIONES SET estado = :estado WHERE id_reservacion = :id")
                ->execute([':estado' => $nuevo_estado, ':id' => $id_reserva]);

            // El auto solo queda 'disponible' si ya no tiene otra reserva en curso
            sincronizar_estado_auto($pdo, $reserva['id_auto'], $hoy);

            $pdo->commit();
            $_SESSION['flash'] = ['tipo' => 'exito', 'texto' => "Estado de la reserva actualizado correctamente."];
        }
    } catch (RuntimeException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        $_SESSION['flash'] = ['tipo' => 'error', 'texto' => $e->getMessage()];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        $_SESSION['flash'] = ['tipo' => 'error', 'texto' => "Error al actualizar: " . $e->getMessage()];
    }

    // Post/Redirect/Get: evita reenviar el formulario al recargar y conserva los filtros
    header("Location: " . url_reservas($filtros, $pagina));
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// ---------------------------------------------------------------
// LEER LAS RESERVAS (con filtros y paginación)
// ---------------------------------------------------------------
$where  = [];
$params = [];

if ($busqueda !== '') {
    $where[] = "(CONCAT(u.nombre, ' ', u.apellidos) LIKE :q1 OR CONCAT(a.marca, ' ', a.modelo) LIKE :q2)";
    $params[':q1'] = '%' . $busqueda . '%';
    $params[':q2'] = '%' . $busqueda . '%';
}
if ($f_estado !== '') {
    $where[] = "r.estado = :estado";
    $params[':estado'] = $f_estado;
}
// Filtro por fechas: reservas cuyo periodo de renta se traslapa con el rango elegido
if ($f_desde !== '') {
    $where[] = "r.fecha_final >= :desde";
    $params[':desde'] = $f_desde;
}
if ($f_hasta !== '') {
    $where[] = "r.fecha_inicio <= :hasta";
    $params[':hasta'] = $f_hasta;
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$from = "FROM RESERVACIONES r
         INNER JOIN USUARIOS u ON r.id_usuario = u.id_usuario
         INNER JOIN AUTOMOVILES a ON r.id_auto = a.id_auto";

$stmt = $pdo->prepare("SELECT COUNT(*) $from $whereSQL");
$stmt->execute($params);
$total_registros = (int)$stmt->fetchColumn();

$total_paginas = max(1, (int)ceil($total_registros / $POR_PAGINA));
$pagina        = min($pagina, $total_paginas);
$offset        = ($pagina - 1) * $POR_PAGINA;

// Más recientes primero (por fecha de reservación; el id desempata)
$stmt = $pdo->prepare("SELECT r.*, u.nombre AS cliente, u.apellidos, a.marca, a.modelo
                       $from
                       $whereSQL
                       ORDER BY r.fecha_reservacion DESC, r.id_reservacion DESC
                       LIMIT $POR_PAGINA OFFSET $offset");
$stmt->execute($params);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$desde_n = $total_registros ? $offset + 1 : 0;
$hasta_n = $offset + count($reservas);

// Páginas a mostrar: 1, anterior, actual, siguiente, última (con "…")
$paginas = array_unique(array_filter([1, $pagina - 1, $pagina, $pagina + 1, $total_paginas],
    function ($p) use ($total_paginas) { return $p >= 1 && $p <= $total_paginas; }));
sort($paginas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Reservaciones - AutoRent Admin</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>

<div class="admin-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-header">
            <div>
                <p style="color: #64748b; font-size: 14px; margin-bottom: 5px;">Dashboard / Reservaciones</p>
                <h1>Gestión de Reservaciones</h1>
            </div>
        </header>

        <!-- Mensajes de éxito o error -->
        <?php if ($flash): ?>
            <div class="alerta alerta-<?php echo $flash['tipo'] === 'exito' ? 'exito' : 'error'; ?>">
                <?php echo htmlspecialchars($flash['texto']); ?>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <form method="GET" action="reservas.php" class="filtros-barra">
            <input type="text" name="q" class="filtro-buscar" placeholder="Buscar por cliente o auto..."
                   value="<?php echo htmlspecialchars($busqueda); ?>">

            <label class="filtro-campo">Estado:
                <select name="estado">
                    <option value="">Todos</option>
                    <?php foreach ($ESTADOS as $e): ?>
                        <option value="<?php echo $e; ?>" <?php echo $f_estado === $e ? 'selected' : ''; ?>>
                            <?php echo ucfirst($e); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="filtro-campo">Desde:
                <input type="date" name="desde" value="<?php echo htmlspecialchars($f_desde); ?>">
            </label>
            <label class="filtro-campo">Hasta:
                <input type="date" name="hasta" value="<?php echo htmlspecialchars($f_hasta); ?>">
            </label>

            <button type="submit" class="btn-primary">Filtrar</button>
            <?php if ($filtros): ?>
                <a href="reservas.php" class="btn-outline">Limpiar</a>
            <?php endif; ?>
        </form>
        <p class="filtro-nota">Las fechas filtran las reservas cuyo periodo de renta se traslapa con el rango elegido.</p>

        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID Reserva</th>
                        <th>Cliente</th>
                        <th>Auto</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($reservas) > 0): ?>
                        <?php foreach ($reservas as $res): ?>
                            <tr>
                                <td>R-<?php echo str_pad($res['id_reservacion'], 4, "0", STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($res['cliente'] . ' ' . $res['apellidos']); ?></td>
                                <td><?php echo htmlspecialchars($res['marca'] . ' ' . $res['modelo']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res['fecha_inicio'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res['fecha_final'])); ?></td>
                                <td>$<?php echo number_format($res['total'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo htmlspecialchars($res['estado']); ?>">
                                        <?php echo ucfirst($res['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="acciones-reserva">
                                        <!-- Cambiar estado -->
                                        <form action="<?php echo htmlspecialchars(url_reservas($filtros, $pagina)); ?>" method="POST" class="form-estado">
                                            <input type="hidden" name="id_reserva" value="<?php echo $res['id_reservacion']; ?>">
                                            <select name="nuevo_estado">
                                                <?php foreach ($ESTADOS as $e): ?>
                                                    <option value="<?php echo $e; ?>" <?php echo $res['estado'] === $e ? 'selected' : ''; ?>>
                                                        <?php echo ucfirst($e); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="cambiar_estado" class="btn-primary btn-chico">Actualizar</button>
                                        </form>

                                        <!-- Cancelar (borrado lógico) -->
                                        <form action="<?php echo htmlspecialchars(url_reservas($filtros, $pagina)); ?>" method="POST"
                                              onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta reserva?');">
                                            <input type="hidden" name="id_reserva" value="<?php echo $res['id_reservacion']; ?>">
                                            <input type="hidden" name="nuevo_estado" value="cancelada">
                                            <button type="submit" name="cambiar_estado" class="btn-accion btn-eliminar btn-chico"
                                                    <?php echo $res['estado'] === 'cancelada' ? 'disabled' : ''; ?>>
                                                Cancelar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">
                                <?php echo $filtros ? 'Ninguna reservación coincide con los filtros.' : 'No hay reservaciones registradas.'; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="paginacion-barra">
            <span>Mostrando <?php echo $desde_n; ?>–<?php echo $hasta_n; ?> de <?php echo $total_registros; ?> registros</span>

            <?php if ($total_paginas > 1): ?>
                <nav class="paginacion">
                    <?php if ($pagina > 1): ?>
                        <a href="<?php echo htmlspecialchars(url_reservas($filtros, $pagina - 1)); ?>">‹</a>
                    <?php endif; ?>

                    <?php $anterior = 0; foreach ($paginas as $p): ?>
                        <?php if ($p - $anterior > 1): ?><span class="puntos">…</span><?php endif; ?>
                        <a href="<?php echo htmlspecialchars(url_reservas($filtros, $p)); ?>"
                           class="<?php echo $p === $pagina ? 'activa' : ''; ?>"><?php echo $p; ?></a>
                        <?php $anterior = $p; ?>
                    <?php endforeach; ?>

                    <?php if ($pagina < $total_paginas): ?>
                        <a href="<?php echo htmlspecialchars(url_reservas($filtros, $pagina + 1)); ?>">›</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>