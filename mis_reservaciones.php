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
                        <button type="button" class="btn-outline btn-ver-detalles" 
                                    data-id="RES-<?php echo str_pad($r['id_reservacion'], 4, "0", STR_PAD_LEFT); ?>"
                                    data-auto="<?php echo htmlspecialchars($r['marca'] . ' ' . $r['modelo']); ?>"
                                    data-inicio="<?php echo date('d/m/Y', strtotime($r['fecha_inicio'])); ?>"
                                    data-fin="<?php echo date('d/m/Y', strtotime($r['fecha_final'])); ?>"
                                    data-dias="<?php echo $r['numero_dias']; ?>"
                                    data-subtotal="$<?php echo number_format($r['subtotal'], 2); ?>"
                                    data-descuento="- $<?php echo number_format($r['descuento'], 2); ?>"
                                    data-total="$<?php echo number_format($r['total'], 2); ?>"
                                    data-estado="<?php echo $r['estado']; ?>">
                                Ver Detalles
                            </button>
                            
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

<!-- MODAL DE DETALLES DE RESERVA -->
<div id="modalDetalles" class="modal-overlay">
    <div class="modal-content">
        <span class="close-modal" onclick="cerrarModal()">&times;</span>
        <h2>Detalles de la Reserva <span id="modal-id"></span></h2>
        
        <div class="modal-body">
            <div class="modal-row">
                <span class="modal-label">Automóvil:</span>
                <strong id="modal-auto"></strong>
            </div>
            <div class="modal-row">
                <span class="modal-label">Fechas:</span>
                <span id="modal-fechas"></span>
            </div>
            <div class="modal-row">
                <span class="modal-label">Días de renta:</span>
                <span id="modal-dias"></span>
            </div>
            
            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 15px 0;">
            
            <div class="modal-row">
                <span class="modal-label">Subtotal:</span>
                <span id="modal-subtotal"></span>
            </div>
            <div class="modal-row">
                <span class="modal-label">Descuento:</span>
                <span id="modal-descuento" style="color: #10b981;"></span>
            </div>
            <div class="modal-row" style="font-size: 18px; font-weight: bold; margin-top: 10px;">
                <span class="modal-label">Total:</span>
                <span id="modal-total"></span>
            </div>
            
            <div class="modal-row" style="margin-top: 20px;">
                <span class="modal-label">Estado:</span>
                <span id="modal-estado" class="badge"></span>
            </div>
        </div>
    </div>
</div>

<script>
    // Seleccionamos todos los botones de "Ver Detalles"
    const botonesDetalles = document.querySelectorAll('.btn-ver-detalles');
    const modal = document.getElementById('modalDetalles');

    botonesDetalles.forEach(boton => {
        boton.addEventListener('click', function() {
            // Extraemos los datos del botón
            const id = this.getAttribute('data-id');
            const auto = this.getAttribute('data-auto');
            const inicio = this.getAttribute('data-inicio');
            const fin = this.getAttribute('data-fin');
            const dias = this.getAttribute('data-dias');
            const subtotal = this.getAttribute('data-subtotal');
            const descuento = this.getAttribute('data-descuento');
            const total = this.getAttribute('data-total');
            const estado = this.getAttribute('data-estado');

            // Llenamos el modal con los datos
            document.getElementById('modal-id').textContent = '#' + id;
            document.getElementById('modal-auto').textContent = auto;
            document.getElementById('modal-fechas').textContent = inicio + ' al ' + fin;
            document.getElementById('modal-dias').textContent = dias + ' días';
            document.getElementById('modal-subtotal').textContent = subtotal;
            document.getElementById('modal-descuento').textContent = descuento;
            document.getElementById('modal-total').textContent = total;
            
            // Formateamos la etiqueta del estado
            const badgeEstado = document.getElementById('modal-estado');
            badgeEstado.textContent = estado.toUpperCase();
            
            // Asignamos la clase de color correcta
            badgeEstado.className = 'badge'; // Reseteamos clases
            if(estado === 'confirmada') badgeEstado.classList.add('badge-confirmada');
            else if(estado === 'cancelada') badgeEstado.classList.add('badge-cancelada');
            else badgeEstado.classList.add('badge-pendiente');

            // Mostramos el modal
            modal.style.display = 'flex';
        });
    });

    // Función para cerrar el modal
    function cerrarModal() {
        modal.style.display = 'none';
    }

    // Cerrar si se hace clic fuera de la ventana blanca
    window.onclick = function(event) {
        if (event.target == modal) {
            cerrarModal();
        }
    }
</script>

<?php require 'includes/footer.php'; ?>