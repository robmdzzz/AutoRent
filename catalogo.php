<?php 
require 'config/conexion.php';
require 'includes/header.php'; 

// --- LÓGICA DE FILTROS ---
// 1. Consulta base: solo autos disponibles
$sql = "SELECT a.*, c.nombre as categoria_nombre 
        FROM AUTOMOVILES a 
        INNER JOIN CATEGORIAS c ON a.id_categoria = c.id_categoria 
        WHERE a.estado <> 'mantenimiento'";

$params = []; // Array para guardar los parámetros de la consulta preparada

// 2. Filtro por Categoría (si viene por la URL)
if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    $sql .= " AND a.id_categoria = :categoria";
    $params[':categoria'] = $_GET['categoria'];
}

// 3. Filtro por Transmisión (si viene por la URL)
if (isset($_GET['transmision']) && !empty($_GET['transmision'])) {
    $sql .= " AND a.transmision = :transmision";
    $params[':transmision'] = $_GET['transmision'];
}

// 4. Ejecutamos la consulta
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$automoviles = $stmt->fetchAll();

// 5. Traemos las categorías de la BD para pintarlas en el menú lateral
$categorias_db = $pdo->query("SELECT * FROM CATEGORIAS")->fetchAll();
?>

<div class="catalogo-container">
    <!-- Barra Lateral de Filtros -->
    <aside class="filtros-sidebar">
        <h2>Filtros</h2>
        
        <!-- Formulario de filtros (Método GET para que los datos viajen por la URL) -->
        <form method="GET" action="catalogo.php">
            
            <div class="filtro-grupo">
                <h3>Categoría</h3>
                <!-- Generamos las categorías dinámicamente desde la BD -->
                <?php foreach($categorias_db as $cat): ?>
                    <label>
                        <input type="radio" name="categoria" value="<?php echo $cat['id_categoria']; ?>" 
                        <?php if(isset($_GET['categoria']) && $_GET['categoria'] == $cat['id_categoria']) echo 'checked'; ?>>
                        <?php echo htmlspecialchars($cat['nombre']); ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="filtro-grupo">
                <h3>Transmisión</h3>
                <label>
                    <input type="radio" name="transmision" value="automatica" 
                    <?php if(isset($_GET['transmision']) && $_GET['transmision'] == 'automatica') echo 'checked'; ?>> Automática
                </label>
                <label>
                    <input type="radio" name="transmision" value="manual" 
                    <?php if(isset($_GET['transmision']) && $_GET['transmision'] == 'manual') echo 'checked'; ?>> Manual
                </label>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 20px; cursor: pointer;">Aplicar filtros</button>
            
            <!-- Botón para limpiar los filtros -->
            <a href="catalogo.php" style="display: block; text-align: center; margin-top: 15px; font-size: 14px; color: #64748b; text-decoration: none;">Limpiar filtros</a>
        </form>
    </aside>

    <!-- Cuadrícula de Autos -->
    <main class="catalogo-main">
        <div class="catalogo-header">
            <h1>Catálogo de Vehículos</h1>
            <span><?php echo count($automoviles); ?> resultados</span>
        </div>

        <div class="autos-grid">
            <?php if(count($automoviles) > 0): ?>
                <!-- Si hay autos, los mostramos -->
                <?php foreach($automoviles as $auto): ?>
                    <div class="auto-card">
                        <!-- Imagen del auto -->
                        <div class="auto-img">
                            <?php if(!empty($auto['imagen']) && file_exists('assets/img/' . $auto['imagen'])): ?>
                                <img src="assets/img/<?php echo $auto['imagen']; ?>" alt="<?php echo $auto['marca']; ?>">
                            <?php else: ?>
                                <!-- Placeholder si no hay imagen o no se encuentra el archivo -->
                                <div class="placeholder-img">X</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="auto-info">
                            <h3><?php echo htmlspecialchars($auto['marca'] . ' ' . $auto['modelo'] . ' ' . $auto['año']); ?></h3>
                            <p class="auto-cat">
                                <?php echo htmlspecialchars($auto['categoria_nombre']); ?> · 
                                <?php echo htmlspecialchars($auto['transmision']); ?>
                            </p>
                            
                            <div class="auto-footer">
                                <span class="precio">$<?php echo number_format($auto['precio_dia'], 2); ?> <span>/día</span></span>
                                <a href="detalle.php?id=<?php echo $auto['id_auto']; ?>" class="btn-outline">Ver Detalles</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Si no hay resultados, mostramos el estado vacío -->
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <h3 style="color: #1e293b; margin-bottom: 10px; font-size: 20px;">No se encontraron vehículos</h3>
                    <p style="color: #64748b; margin-bottom: 20px;">Intenta cambiando los filtros de búsqueda o limpia los filtros.</p>
                    <a href="catalogo.php" class="btn-primary" style="text-decoration: none; padding: 10px 20px; display: inline-block;">Ver todos los autos</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require 'includes/footer.php'; ?>