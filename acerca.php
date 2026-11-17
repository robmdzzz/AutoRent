<?php 
require 'config/conexion.php';
require 'includes/header.php'; 
?>

<div class="acerca-container">
    <!-- Sección Hero -->
    <section class="acerca-hero">
        <p style="color: #64748b; font-weight: bold; letter-spacing: 1px; margin-bottom: 10px;">NOSOTROS</p>
        <h1 style="font-size: 36px; color: #1e293b; margin-bottom: 40px;">Acerca de AutoRent</h1>
        
        <div class="acerca-intro">
            <div class="intro-img placeholder-img" style="height: 300px; border-radius: 8px;">X</div>
            <div class="intro-texto">
                <h2>Somos una plataforma dedicada a facilitar la renta de automóviles en México, ofreciendo vehículos de calidad y un servicio excepcional.</h2>
                <a href="catalogo.php" class="btn-primary" style="display: inline-block; margin-top: 20px; text-decoration: none; padding: 10px 20px;">Ver Catálogo</a>
            </div>
        </div>
    </section>

    <!-- Sección Misión, Visión y Valores -->
    <section class="acerca-mvv" style="background-color: #f8fafc; padding: 60px 20px; text-align: center;">
        <h2 style="margin-bottom: 40px; color: #1e293b;">Misión, Visión y Valores</h2>
        <div class="mvv-grid">
            <div class="mvv-card">
                <div></div>
                <h3>Misión</h3>
                <p>Ofrecer soluciones de movilidad accesibles y confiables.</p>
            </div>
            <div class="mvv-card">
                <div></div>
                <h3>Visión</h3>
                <p>Ser la plataforma de renta de autos líder en el país.</p>
            </div>
            <div class="mvv-card">
                <div></div>
                <h3>Valores</h3>
                <p>Compromiso, transparencia y excelencia en el servicio.</p>
            </div>
        </div>
    </section>

    <!-- Sección Nuestra Historia -->
    <section class="acerca-historia" style="max-width: 800px; margin: 60px auto; padding: 0 20px; line-height: 1.8; color: #475569;">
        <h2 style="color: #1e293b; margin-bottom: 20px;">Nuestra Historia</h2>
        <p style="margin-bottom: 15px;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
        <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
    </section>
</div>

<?php require 'includes/footer.php'; ?>