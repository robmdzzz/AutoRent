<?php
session_start();
// Subimos un nivel (../) para encontrar la carpeta config
require '../config/conexion.php';

date_default_timezone_set('America/Mexico_City');

// Solo administradores (antes esta página no validaba la sesión)
if (!isset($_SESSION['id_usuario']) || ($_SESSION['rol'] ?? '') !== 'administrador') {
    header("Location: /login.php");
    exit;
}

// ---------------------------------------------------------------
// Utilidades de fecha en español
// ---------------------------------------------------------------
$meses_abrev  = [1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun',
                 7=>'Jul', 8=>'Ago', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'];
$meses_largos = [1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril', 5=>'Mayo', 6=>'Junio',
                 7=>'Julio', 8=>'Agosto', 9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'];

function fecha_es($fecha, $meses) {
    $d = new DateTimeImmutable($fecha);
    return $d->format('d') . ' ' . $meses[(int)$d->format('n')] . ' ' . $d->format('Y');
}

// ---------------------------------------------------------------
// 1. SELECTOR DE PERIODO  ->  rango [$desde, $hasta] (ambos incluidos)
// ---------------------------------------------------------------
$hoy        = new DateTimeImmutable('today');
$inicio_mes = $hoy->modify('first day of this month');

$periodo = $_GET['periodo'] ?? 'este_mes';
if (!in_array($periodo, ['este_mes', 'mes_pasado', '3_meses', 'personalizado'], true)) {
    $periodo = 'este_mes';
}

if ($periodo === 'personalizado') {
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $_GET['desde'] ?? '');
    $h = DateTimeImmutable::createFromFormat('!Y-m-d', $_GET['hasta'] ?? '');
    if ($d && $h) {
        if ($d > $h) { [$d, $h] = [$h, $d]; }   // si las pusieron al revés
        $desde = $d;
        $hasta = $h;
    } else {
        $periodo = 'este_mes';                    // rango inválido: volvemos al default
    }
}

if ($periodo === 'este_mes') {
    $desde = $inicio_mes;
    $hasta = $inicio_mes->modify('last day of this month');
} elseif ($periodo === 'mes_pasado') {
    $desde = $inicio_mes->modify('-1 month');
    $hasta = $inicio_mes->modify('-1 day');
} elseif ($periodo === '3_meses') {
    // mes actual + los 2 anteriores
    $desde = $inicio_mes->modify('-2 months');
    $hasta = $hoy;
}

$hasta_excl = $hasta->modify('+1 day');          // límite superior exclusivo para el SQL

if ($periodo === 'este_mes' || $periodo === 'mes_pasado') {
    $etiqueta_periodo = $meses_largos[(int)$desde->format('n')] . ' ' . $desde->format('Y');
} else {
    $etiqueta_periodo = fecha_es($desde->format('Y-m-d'), $meses_abrev) . ' – ' .
                        fecha_es($hasta->format('Y-m-d'), $meses_abrev);
}

// ---------------------------------------------------------------
// 2. MÉTRICAS
// ---------------------------------------------------------------
// Total de autos: tiempo real
$total_autos = $pdo->query("SELECT COUNT(*) FROM AUTOMOVILES")->fetchColumn();

// Reservas activas: confirmadas o pendientes que AÚN están vigentes
// (la renta no ha terminado). Tiempo real, no depende del periodo.
$stmt = $pdo->prepare("SELECT COUNT(*) FROM RESERVACIONES
                       WHERE estado IN ('confirmada', 'pendiente')
                       AND fecha_final >= :hoy");
$stmt->execute([':hoy' => $hoy->format('Y-m-d')]);
$reservas_activas = $stmt->fetchColumn();

// Ingresos del periodo seleccionado (solo confirmadas)
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) FROM RESERVACIONES
                       WHERE estado = 'confirmada'
                       AND fecha_reservacion >= :desde
                       AND fecha_reservacion <  :hasta");
$stmt->execute([':desde' => $desde->format('Y-m-d'), ':hasta' => $hasta_excl->format('Y-m-d')]);
$ingresos_periodo = $stmt->fetchColumn();

// ---------------------------------------------------------------
// 3. ÚLTIMAS 5 RESERVAS del periodo seleccionado
// ---------------------------------------------------------------
$stmt = $pdo->prepare("SELECT r.*, u.nombre AS cliente, a.marca, a.modelo
                       FROM RESERVACIONES r
                       INNER JOIN USUARIOS u ON r.id_usuario = u.id_usuario
                       INNER JOIN AUTOMOVILES a ON r.id_auto = a.id_auto
                       WHERE r.fecha_reservacion >= :desde
                       AND r.fecha_reservacion <  :hasta
                       ORDER BY r.fecha_reservacion DESC
                       LIMIT 5");
$stmt->execute([':desde' => $desde->format('Y-m-d'), ':hasta' => $hasta_excl->format('Y-m-d')]);
$ultimas_reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------------------------------------------
// 4. GRÁFICA: últimos 6 meses (incluye el actual), fija como referencia.
//    Agrupa por AÑO y mes, rellena con 0 los meses sin ingresos y
//    resalta los meses que caen dentro del periodo seleccionado.
// ---------------------------------------------------------------
$inicio_grafica = $inicio_mes->modify('-5 months');
$fin_grafica    = $inicio_mes->modify('+1 month');

$stmt = $pdo->prepare("SELECT YEAR(fecha_reservacion) AS anio, MONTH(fecha_reservacion) AS mes,
                              SUM(total) AS ingresos
                       FROM RESERVACIONES
                       WHERE estado = 'confirmada'
                       AND fecha_reservacion >= :ini
                       AND fecha_reservacion <  :fin
                       GROUP BY YEAR(fecha_reservacion), MONTH(fecha_reservacion)");
$stmt->execute([':ini' => $inicio_grafica->format('Y-m-d'), ':fin' => $fin_grafica->format('Y-m-d')]);

$ingresos_por_mes = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
    $ingresos_por_mes[sprintf('%04d-%02d', $f['anio'], $f['mes'])] = (float)$f['ingresos'];
}

$labels_meses     = [];
$valores_ingresos = [];
$colores_barras   = [];
for ($k = 0; $k < 6; $k++) {
    $m       = $inicio_grafica->modify("+$k months");
    $fin_mes = $m->modify('last day of this month');
    $n       = (int)$m->format('n');

    $etq = $meses_abrev[$n];
    if ($k === 0 || $n === 1) { $etq .= ' ' . $m->format('Y'); }   // año al inicio y en enero

    $labels_meses[]     = $etq;
    $valores_ingresos[] = $ingresos_por_mes[$m->format('Y-m')] ?? 0;
    $colores_barras[]   = ($m <= $hasta && $fin_mes >= $desde) ? '#1e3a8a' : '#cbd5e1';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - AutoRent Admin</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>

<div class="admin-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-header">
            <h1>Panel de Control</h1>
            <div class="admin-user" style="display: flex; align-items: center; gap: 20px;">

                <!-- Selector de periodo: envía el formulario por GET al cambiar -->
                <form method="GET" action="dashboard.php" id="form-periodo" style="display: flex; align-items: center; gap: 8px;">
                    <select name="periodo" id="periodo" class="form-select" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; color: #475569; font-family: Arial;">
                        <option value="este_mes"      <?php echo $periodo === 'este_mes' ? 'selected' : ''; ?>>Este Mes</option>
                        <option value="mes_pasado"    <?php echo $periodo === 'mes_pasado' ? 'selected' : ''; ?>>Mes Pasado</option>
                        <option value="3_meses"       <?php echo $periodo === '3_meses' ? 'selected' : ''; ?>>Últimos 3 meses</option>
                        <option value="personalizado" <?php echo $periodo === 'personalizado' ? 'selected' : ''; ?>>Rango personalizado</option>
                    </select>

                    <span id="rango-personalizado" style="display: <?php echo $periodo === 'personalizado' ? 'flex' : 'none'; ?>; align-items: center; gap: 6px;">
                        <input type="date" name="desde" value="<?php echo $desde->format('Y-m-d'); ?>" style="padding: 7px; border-radius: 6px; border: 1px solid #cbd5e1;">
                        <span>a</span>
                        <input type="date" name="hasta" value="<?php echo $hasta->format('Y-m-d'); ?>" style="padding: 7px; border-radius: 6px; border: 1px solid #cbd5e1;">
                        <button type="submit" class="btn-primary" style="padding: 8px 12px; border-radius: 6px; cursor: pointer;">Aplicar</button>
                    </span>
                </form>

                <span>Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></span>
            </div>
        </header>

        <!-- Tarjetas de Métricas -->
        <div class="metricas-grid">
            <div class="metrica-card">
                <span class="metrica-titulo">Total de Autos</span>
                <h2 class="metrica-valor"><?php echo $total_autos; ?></h2>
            </div>
            <div class="metrica-card">
                <span class="metrica-titulo">Reservas Activas</span>
                <h2 class="metrica-valor"><?php echo $reservas_activas; ?></h2>
                <small style="color: #64748b;">Confirmadas o pendientes vigentes</small>
            </div>
            <div class="metrica-card">
                <span class="metrica-titulo">Ingresos del Periodo</span>
                <h2 class="metrica-valor">$<?php echo number_format($ingresos_periodo, 2); ?></h2>
                <small style="color: #64748b;"><?php echo htmlspecialchars($etiqueta_periodo); ?></small>
            </div>
        </div>

        <!-- Gráfica Comparativa (últimos 6 meses, resalta el periodo seleccionado) -->
        <div class="admin-card" style="margin-bottom: 30px;">
            <h3>Comparativa de Ingresos por Mes</h3>
            <p style="color: #64748b; font-size: 13px; margin: 4px 0 10px;">Vista seleccionada: <?php echo htmlspecialchars($etiqueta_periodo); ?></p>
            <div style="width: 100%; height: 300px;">
                <canvas id="graficaIngresos"></canvas>
            </div>
        </div>

        <!-- Tabla de Últimas Reservas -->
        <div class="admin-card">
            <h3>Últimas reservas · <?php echo htmlspecialchars($etiqueta_periodo); ?></h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Auto</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($ultimas_reservas) > 0): ?>
                        <?php foreach ($ultimas_reservas as $res): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($res['cliente']); ?></td>
                                <td><?php echo htmlspecialchars($res['marca'] . ' ' . $res['modelo']); ?></td>
                                <td><?php echo fecha_es($res['fecha_reservacion'], $meses_abrev); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo htmlspecialchars($res['estado']); ?>">
                                        <?php echo ucfirst($res['estado']); ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($res['total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No hay reservas en este periodo.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Selector de periodo -->
<script>
    const selPeriodo = document.getElementById('periodo');
    const rangoPers  = document.getElementById('rango-personalizado');
    selPeriodo.addEventListener('change', () => {
        if (selPeriodo.value === 'personalizado') {
            rangoPers.style.display = 'flex';          // espera a que elija las fechas y presione "Aplicar"
        } else {
            document.getElementById('form-periodo').submit();
        }
    });
</script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficaIngresos').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels_meses); ?>,
            datasets: [{
                label: 'Ingresos ($)',
                data: <?php echo json_encode($valores_ingresos); ?>,
                backgroundColor: <?php echo json_encode($colores_barras); ?>,
                borderRadius: 6,
                barThickness: 50
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (c) => '$' + c.parsed.y.toLocaleString('es-MX', {minimumFractionDigits: 2})
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { callback: (v) => '$' + v.toLocaleString('es-MX') }
                },
                x: { grid: { display: false } }
            }
        }
    });
</script>

</body>
</html>