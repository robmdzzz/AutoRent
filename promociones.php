<?php 
require 'config/conexion.php';
require 'includes/header.php'; 

// Consultamos las promociones vigentes
$fecha_actual = date('Y-m-d');
$sql = "SELECT * FROM PROMOCIONES 
        WHERE estado = 'activa' 
        AND fecha_inicio <= :fecha_actual 
        AND fecha_fin >= :fecha_actual";
$stmt = $pdo->prepare($sql);
$stmt->execute([':fecha_actual' => $fecha_actual]);
$promociones = $stmt->fetchAll();
?>

<div class="promociones-container">
    <div style="text-align: center; margin-bottom: 40px;">
        <p style="color: #64748b; font-weight: bold; letter-spacing: 1px;">OFERTAS ESPECIALES</p>
        <h1 style="font-size: 36px; color: #1e293b; margin-top: 10px;">Promociones y Descuentos Vigentes</h1>
        <p style="color: #64748b; margin-top: 10px;">Aprovecha nuestras ofertas especiales en la renta de vehículos.</p>
    </div>

    <div class="promos-grid">
        <?php if(count($promociones) > 0): ?>
            <?php foreach($promociones as $promo): ?>
                <div class="promo-card">
                    <div class="promo-header">
                        <span class="promo-badge"><?php echo number_format($promo['porcentaje_descuento'], 0); ?>% OFF</span>
                    </div>
                    <div class="promo-body">
                        <h3><?php echo htmlspecialchars($promo['nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($promo['descripcion']); ?></p>
                        
                        <div class="promo-fecha">
                            <strong>Válido hasta:</strong> <?php echo date('d M Y', strtotime($promo['fecha_fin'])); ?>
                        </div>

                        <a href="catalogo.php" class="btn-primary" style="display: block; text-align: center; text-decoration: none; padding: 12px; margin-top: 20px;">
                            Ver Autos en Promoción
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                <h3 style="color: #1e293b; margin-bottom: 10px; font-size: 20px;">No hay promociones vigentes</h3>
                <p style="color: #64748b; margin-bottom: 20px;">Vuelve a revisar pronto, estamos preparando nuevas ofertas.</p>
                <a href="catalogo.php" class="btn-outline" style="text-decoration: none; padding: 10px 20px;">Ver Catálogo</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require 'includes/footer.php'; ?>