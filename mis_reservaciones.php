<?php 
require 'config/conexion.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: /login.php");
    exit;
}

// Traer las reservas del usuario logueado
$sql = "SELECT r.*, a.marca, a.modelo, a.imagen 
        FROM RESERVACIONES r 
        INNER JOIN AUTOMOVILES a ON r.id_auto = a.id_auto 
        WHERE r.id_usuario = :id_usuario 
        ORDER BY r.fecha_reservacion DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id_usuario' => $_SESSION['id_usuario']]);
$reservas = $stmt->fetchAll();

require 'includes/header.php'; 
?>

<div class="container" style="max-width: 1000px; margin: 40px auto; padding: 0 20px;">
    <h1 style="color: #1e293b; margin-bottom: 30px;">Mis Reservaciones</h1>

    <?php if(count($reservas) == 0): ?>
        <div style="background: white; padding: 40px; text-align: center; border-radius: 8px; border: 1px solid #e2e8f0;">
            <p style="color: #64748b; margin-bottom: 20px;">Aún no tienes reservaciones registradas.</p>
            <a href="/catalogo.php" class="btn-primary" style="text-decoration: none; padding: 10px 20px;">Explorar Catálogo</a>
        </div>
    <?php else: ?>
        <div class="reservas-list">
            <?php foreach($reservas as $r): ?>
                <div class="reserva-card">
                    <!-- Imagen -->
                    <div class="reserva-img">
                        <?php if(!empty($r['imagen']) && file_exists('assets/img/' . $r['imagen'])): ?>
                            <img src="/assets/img/<?php echo $r['imagen']; ?>" alt="Auto">
                        <?php else: ?>
                            <div class="placeholder-img">X</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Info -->
                    <div class="reserva-info">
                        <h3><?php echo htmlspecialchars($r['marca'] . ' ' . $r['modelo']); ?></h3>
                        <p class="fechas">
                            <?php echo date('d M Y', strtotime($r['fecha_inicio'])); ?> - 
                            <?php echo date('d M Y', strtotime($r['fecha_final'])); ?> | 
                            Reserva #<?php echo $r['id_reservacion']; ?>
                        </p>
                        
                        <!-- Etiqueta de Estado -->
                        <?php 
                            $color = '#fef08a'; // Amarillo por defecto (Pendiente)
                            $texto = '#854d0e';
                            if($r['estado'] == 'confirmada') { $color = '#d1fae5'; $texto = '#065f46'; }
                            if($r['estado'] == 'cancelada') { $color = '#fee2e2'; $texto = '#991b1b'; }
                        ?>
                        <span style="background: <?php echo $color; ?>; color: <?php echo $texto; ?>; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: capitalize;">
                            <?php echo $r['estado']; ?>
                        </span>
                    </div>

                    <!-- Precio y Acciones -->
                    <div class="reserva-acciones">
                        <div class="precio">
                            <span>Precio total</span>
                            <strong>$<?php echo number_format($r['total'], 2); ?></strong>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button class="btn-outline" onclick="alert('Aquí iría el modal con los detalles')">Ver Detalles</button>
                            
                            <?php if($r['estado'] != 'cancelada'): ?>
                                <form action="/cancelar_reserva.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta reserva?');">
                                    <input type="hidden" name="id_reserva" value="<?php echo $r['id_reservacion']; ?>">
                                    <button type="submit" class="btn-outline" style="color: #ef4444; border-color: #ef4444;">Cancelar Reserva</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .reserva-card {
        display: flex; background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px; align-items: center; gap: 20px;
    }
    .reserva-img { width: 150px; height: 100px; background: #f1f5f9; border-radius: 6px; overflow: hidden; flex-shrink: 0; }
    .reserva-img img { width: 100%; height: 100%; object-fit: cover; }
    .reserva-info { flex-grow: 1; }
    .reserva-info h3 { margin-bottom: 5px; color: #1e293b; }
    .reserva-info .fechas { color: #64748b; font-size: 14px; margin-bottom: 10px; }
    .reserva-acciones { text-align: right; display: flex; flex-direction: column; gap: 15px; }
    .reserva-acciones .precio span { display: block; font-size: 12px; color: #64748b; }
    .reserva-acciones .precio strong { font-size: 20px; color: #1e293b; }
</style>

<?php require 'includes/footer.php'; ?>