<?php 
require 'config/conexion.php';
require 'includes/header.php'; 

// Consulta para traer todos los autos disponibles junto con su categoría
$sql = "SELECT a.*, c.nombre as categoria_nombre 
        FROM AUTOMOVILES a 
        INNER JOIN CATEGORIAS c ON a.id_categoria = c.id_categoria 
        WHERE a.estado = 'disponible'";
$stmt = $pdo->query($sql);
$automoviles = $stmt->fetchAll();
?>

<div class="catalogo-container">
    <!-- Barra Lateral de Filtros  -->
    <aside class="filtros-sidebar">
        <h2>Filtros</h2>
        
        <div class="filtro-grupo">
            <h3>Categoría</h3>
            <label><input type="checkbox"> SUV</label>
            <label><input type="checkbox"> Deportivo</label>
            <label><input type="checkbox"> Sedán</label>
        </div>

        <div class="filtro-grupo">
            <h3>Transmisión</h3>
            <label><input type="checkbox"> Automática</label>
            <label><input type="checkbox"> Manual</label>
        </div>

        <button class="btn-primary" style="width: 100%; margin-top: 20px;">Aplicar filtros</button>
    </aside>

    <!-- Cuadrícula de Autos -->
    <main class="catalogo-main">
        <div class="catalogo-header">
            <h1>Catálogo de Vehículos</h1>
            <span><?php echo count($automoviles); ?> resultados</span>
        </div>

        <div class="autos-grid">
            <?php foreach($automoviles as $auto): ?>
                <div class="auto-card">
                    <!-- Imagen del auto -->
                    <div class="auto-img">
                        <?php if(!empty($auto['imagen']) && file_exists('assets/img/' . $auto['imagen'])): ?>
                            <img src="assets/img/<?php echo $auto['imagen']; ?>" alt="<?php echo $auto['marca']; ?>">
                        <?php else: ?>
                            <!-- Placeholder si no hay imagen -->
                            <div class="placeholder-img">X</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="auto-info">
                        <h3><?php echo htmlspecialchars($auto['marca'] . ' ' . $auto['modelo'] . ' ' . $auto['año']); ?></h3>
                        <p class="auto-cat"><?php echo htmlspecialchars($auto['categoria_nombre']); ?> · <?php echo htmlspecialchars($auto['transmision']); ?></p>
                        
                        <div class="auto-footer">
                            <span class="precio">$<?php echo number_format($auto['precio_dia'], 2); ?> <span>/día</span></span>
                            <a href="detalle.php?id=<?php echo $auto['id_auto']; ?>" class="btn-outline">Ver Detalles</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<?php require 'includes/footer.php'; ?>