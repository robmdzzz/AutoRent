<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoRent - Renta de Autos</title>
    <link rel="stylesheet" href="../assets/css/estilo.css"> <!-- Ajusta esta ruta si es necesario -->
</head>
<body>
    <header class="navbar">
        <!-- 1. Logo (Izquierda) -->
        <div class="logo">
            <a href="/index.php">AutoRent</a>
        </div>
        
        <!-- 2. Enlaces del Centro -->
        <nav class="nav-links">
            <a href="/index.php">Inicio</a>
            <a href="/catalogo.php">Catálogo</a>
            <a href="/promociones.php">Promociones</a>
            
            <?php if(isset($_SESSION['id_usuario']) && $_SESSION['rol'] == 'cliente'): ?>
                <!-- Si es un cliente, mostramos Mis Reservaciones en el menú principal -->
                <a href="/mis_reservaciones.php">Mis Reservaciones</a>
            <?php else: ?>
                <!-- Si es invitado, mostramos las estáticas -->
                <a href="/acerca.php">Acerca de</a>
                <a href="/contacto.php">Contacto</a>
            <?php endif; ?>
        </nav>

        <!-- 3. Botones de Usuario (Derecha) -->
        <div class="nav-auth">
            <?php if(isset($_SESSION['id_usuario'])): ?>
                <span>Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                
                <?php if($_SESSION['rol'] == 'administrador'): ?>
                    <a href="/admin/dashboard.php" class="btn-primary">Panel Admin</a>
                <?php endif; ?>
                
                <a href="/logout.php" class="btn-outline">Cerrar Sesión</a>
            <?php else: ?>
                <a href="/login.php" class="btn-outline">Iniciar Sesión</a>
                <a href="/login.php" class="btn-primary">Registrarse</a>
            <?php endif; ?>
        </div>
    </header>
    <main>