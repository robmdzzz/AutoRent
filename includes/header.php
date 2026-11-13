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

    <link rel="stylesheet" type="text/css" href="assets\css\estilo.css">
</head>
<body>
    <header class="navbar">
        <div class="logo">
            <a href="/autorent/index.php">AutoRent</a>
        </div>
        
        <nav class="nav-links">
            <a href="/autorent/index.php">Inicio</a>
            <a href="/autorent/catalogo.php">Catálogo</a>
            <a href="/autorent/promociones.php">Promociones</a>
            <a href="/autorent/acerca.php">Acerca de</a>
            <a href="/autorent/contacto.php">Contacto</a>
        </nav>

        <div class="nav-auth">
            <?php if(isset($_SESSION['id_usuario'])): ?>
                <!-- Si el usuario está logueado -->
                <span>Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                <?php if($_SESSION['rol'] == 'administrador'): ?>
                    <a href="/autorent/admin/dashboard.php" class="btn-primary">Panel Admin</a>
                <?php endif; ?>
                <a href="/autorent/logout.php" class="btn-outline">Cerrar Sesión</a>
            <?php else: ?>
                <!-- Si no hay sesión -->
                <a href="/autorent/login.php" class="btn-outline">Iniciar Sesión</a>
                <a href="/autorent/login.php" class="btn-primary">Registrarse</a>
            <?php endif; ?>
        </div>
    </header>
    <main>