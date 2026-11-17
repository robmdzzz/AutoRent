<?php
// Verificamos que sea administrador, si no, lo mandamos al login
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'administrador') {
    header("Location: /login.php");
    exit;
}

// Obtenemos el nombre del archivo actual para resaltar el menú activo
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
        <div class="admin-logo">
        <!-- Enlace al index público -->
        <a href="/index.php" style="text-decoration: none; color: inherit; display: block; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
            <h2>AutoRent</h2>
        </a>
        <span>ADMINISTRADOR</span>
    </div>

    <nav class="admin-nav">
        <a href="/admin/dashboard.php" class="<?php echo ($pagina_actual == 'dashboard.php') ? 'active' : ''; ?>">
            Dashboard
        </a>
        <a href="/admin/autos.php" class="<?php echo ($pagina_actual == 'autos.php') ? 'active' : ''; ?>">
            Automóviles
        </a>
        <a href="/admin/reservas.php" class="<?php echo ($pagina_actual == 'reservas.php') ? 'active' : ''; ?>">
            Reservaciones
        </a>
        <a href="/admin/usuarios.php" class="<?php echo ($pagina_actual == 'usuarios.php') ? 'active' : ''; ?>">
            Usuarios
        </a>
        <a href="/admin/promociones.php" class="<?php echo ($pagina_actual == 'promociones.php' || $pagina_actual == 'promo_form.php') ? 'active' : ''; ?>">
            Promociones
        </a>
    </nav>

    <div class="admin-logout">
        <a href="/logout.php" class="btn-outline" style="display:block; text-align:center;">Cerrar Sesión</a>
    </div>
</aside>