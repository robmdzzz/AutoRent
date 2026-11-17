<?php 
require 'config/conexion.php'; 

// Consulta para traer las categorías y una imagen de ejemplo de cada una
$sql_categorias = "SELECT c.id_categoria, c.nombre, c.descripcion, 
                   (SELECT imagen FROM AUTOMOVILES WHERE id_categoria = c.id_categoria LIMIT 1) as imagen_ejemplo
                   FROM CATEGORIAS c 
                   LIMIT 3";
$categorias_populares = $pdo->query($sql_categorias)->fetchAll();

require 'includes/header.php'; 
?>

<section class="hero" style="text-align: center; padding: 80px 20px; background-color: #f1f5f9;">
    <h1 style="font-size: 48px; margin-bottom: 20px;">Renta el auto de tus sueños</h1>
    <p style="color: #64748b; margin-bottom: 40px;">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
    
    <!-- Buscador placeholder -->
    <div style="background: white; padding: 20px; display: inline-block; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <a href="/catalogo.php" class="btn-primary" style="padding: 12px 24px; font-size: 16px;">Buscar Disponibilidad</a>
    </div>
</section>

<div style="max-width: 1200px; margin: 60px auto; padding: 0 20px;">
    <h2 style="margin-bottom: 30px; font-size: 28px; color: #1e293b;">Categorías Populares</h2>
    
    <div class="categorias-grid">
        <?php foreach($categorias_populares as $cat): ?>
            <div class="categoria-card">
                <!-- Imagen de la categoría (tomada del primer auto que encuentre) -->
                <div class="cat-img">
                    <?php if($cat['imagen_ejemplo'] && file_exists('assets/img/' . $cat['imagen_ejemplo'])): ?>
                        <img src="assets/img/<?php echo $cat['imagen_ejemplo']; ?>" alt="<?php echo $cat['nombre']; ?>">
                    <?php else: ?>
                        <div class="placeholder-img">X</div>
                    <?php endif; ?>
                </div>
                
                <div class="cat-info">
                    <h3><?php echo htmlspecialchars($cat['nombre']); ?></h3>
                    <p><?php echo htmlspecialchars($cat['descripcion']); ?></p>
                    <!-- El botón lleva al catálogo con el filtro ya aplicado -->
                    <a href="catalogo.php?categoria=<?php echo $cat['id_categoria']; ?>" class="btn-outline">Ver más</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require 'includes/footer.php'; ?>