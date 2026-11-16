<?php
session_start();
// Subimos un nivel (../) para encontrar la carpeta config
require '../config/conexion.php'; 

// 1. Consultas para las métricas reales
$total_autos = $pdo->query("SELECT COUNT(*) FROM AUTOMOVILES")->fetchColumn();

$reservas_activas = $pdo->query("SELECT COUNT(*) FROM RESERVACIONES WHERE estado IN ('confirmada', 'pendiente')")->fetchColumn();

$sql_ingresos = "SELECT SUM(total) FROM RESERVACIONES 
                 WHERE estado = 'confirmada' 
                 AND MONTH(fecha_reservacion) = MONTH(CURRENT_DATE()) 
                 AND YEAR(fecha_reservacion) = YEAR(CURRENT_DATE())";
$ingresos_mes = $pdo->query($sql_ingresos)->fetchColumn();
$ingresos_mes = $ingresos_mes ? $ingresos_mes : 0;

// 2. Consulta para las últimas 5 reservas
$sql_ultimas = "SELECT r.*, u.nombre as cliente, a.marca, a.modelo 
                FROM RESERVACIONES r 
                INNER JOIN USUARIOS u ON r.id_usuario = u.id_usuario 
                INNER JOIN AUTOMOVILES a ON r.id_auto = a.id_auto 
                ORDER BY r.fecha_reservacion DESC LIMIT 5";
$ultimas_reservas = $pdo->query($sql_ultimas)->fetchAll();

// 3. Consulta para la Gráfica (Ingresos de los últimos 6 meses)
$sql_grafica = "SELECT MONTH(fecha_reservacion) as mes_num, SUM(total) as ingresos 
                FROM RESERVACIONES 
                WHERE estado = 'confirmada' 
                GROUP BY MONTH(fecha_reservacion) 
                ORDER BY MONTH(fecha_reservacion) DESC LIMIT 6";
$datos_grafica = $pdo->query($sql_grafica)->fetchAll();

// Invertimos el array para que los meses vayan del más antiguo al más reciente
$datos_grafica = array_reverse($datos_grafica);

// Mapeo de meses a español
$meses_espanol = [1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Ago', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'];

$labels_meses = [];
$valores_ingresos = [];

foreach($datos_grafica as $d) {
    $labels_meses[] = $meses_espanol[$d['mes_num']];
    $valores_ingresos[] = $d['ingresos'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - AutoRent Admin</title>
    <!-- Asegúrate de que esta ruta sea la correcta para tu CSS -->
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>

<div class="admin-layout">
    <!-- Incluimos el Sidebar (menú lateral) -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Contenido Principal -->
    <main class="admin-main">
        <header class="admin-header">
            <h1>Panel de Control</h1>
            <div class="admin-user" style="display: flex; align-items: center; gap: 20px;">
                <!-- Selector de periodo -->
                <select class="form-select" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; color: #475569; font-family: Arial;">
                    <option>Este Mes</option>
                    <option>Mes Pasado</option>
                    <option>Últimos 3 meses</option>
                </select>
                <span>Hola, <strong><?php echo $_SESSION['nombre']; ?></strong></span>
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
            </div>
            <div class="metrica-card">
                <span class="metrica-titulo">Ingresos del Mes</span>
                <h2 class="metrica-valor">$<?php echo number_format($ingresos_mes, 2); ?></h2>
            </div>
        </div>

        <!-- NUEVA SECCIÓN: Gráfica Comparativa -->
        <div class="admin-card" style="margin-bottom: 30px;">
            <h3>Comparativa de Ingresos por Mes</h3>
            <div style="width: 100%; height: 300px;">
                <canvas id="graficaIngresos"></canvas>
            </div>
        </div>

        <!-- Tabla de Últimas Reservas -->
        <div class="admin-card">
            <h3>Últimas reservas</h3>
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
                    <?php if(count($ultimas_reservas) > 0): ?>
                        <?php foreach($ultimas_reservas as $res): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($res['cliente']); ?></td>
                                <td><?php echo htmlspecialchars($res['marca'] . ' ' . $res['modelo']); ?></td>
                                <td><?php echo date('d M Y', strtotime($res['fecha_reservacion'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $res['estado']; ?>">
                                        <?php echo ucfirst($res['estado']); ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($res['total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No hay reservas registradas aún.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Script de Chart.js para la gráfica -->
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
                backgroundColor: '#1e3a8a', // Azul oscuro de AutoRent
                borderRadius: 6,
                barThickness: 50 // Grosor de las barras
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
</script>

</body>
</html>