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
    <link rel="stylesheet" href="../assets/css/estilo.css">
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
                
                <!-- MENÚ DESPLEGABLE DE USUARIO -->
                <div class="user-dropdown">
                <button class="user-trigger" id="btn-user-dropdown">
                Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?> <span>▼</span>
                </button>
                    
                    <div class="dropdown-menu" id="myDropdown">
                        <?php if($_SESSION['rol'] == 'administrador'): ?>
                            <a href="/admin/dashboard.php">Panel Admin</a>
                        <?php else: ?>
                            <a href="/perfil.php">Mi Perfil</a>
                        <?php endif; ?>
                        <a href="/logout.php" style="color: #ef4444;">Cerrar Sesión</a>
                    </div>
                </div>

            <?php else: ?>
                <a href="/login.php" class="btn-outline">Iniciar Sesión</a>
                <a href="/login.php" class="btn-primary">Registrarse</a>
            <?php endif; ?>
        </div>
    </header>
    <main>

            <!-- Script para el dropdown de usuario -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const btn = document.getElementById("btn-user-dropdown");
            const menu = document.getElementById("myDropdown");

            if (btn && menu) {
                // Al hacer clic en el botón, abrimos o cerramos el menú
                btn.addEventListener("click", function(e) {
                    e.stopPropagation(); // Evita que el clic se propague y lo cierre
                    menu.classList.toggle("show");
                });

                // Al hacer clic en cualquier otro lado, cerramos el menú
                document.addEventListener("click", function(e) {
                    if (!e.target.closest(".user-trigger")) {
                        if (menu.classList.contains("show")) {
                            menu.classList.remove("show");
                        }
                    }
                });
            }
        });
    </script>