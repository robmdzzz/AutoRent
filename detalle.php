<?php 
require 'config/conexion.php';
require 'includes/header.php'; 

// Validar que venga un ID por la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: catalogo.php");
    exit;
}

$id_auto = $_GET['id'];

// Consultar el auto específico
$sql = "SELECT a.*, c.nombre as categoria_nombre 
        FROM AUTOMOVILES a 
        INNER JOIN CATEGORIAS c ON a.id_categoria = c.id_categoria 
        WHERE a.id_auto = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_auto]);
$auto = $stmt->fetch();

// Si el auto no existe, regresar al catálogo
if (!$auto) {
    header("Location: catalogo.php");
    exit;
}
?>

<div class="detalle-container">
        <!-- Columna Izquierda: Galería de Fotos -->
    <div class="detalle-galeria">
        <div class="foto-principal">
            <?php if(!empty($auto['imagen']) && file_exists('assets/img/' . $auto['imagen'])): ?>
                <!-- Usamos object-fit contain para que la imagen no se corte -->
                <img src="assets/img/<?php echo $auto['imagen']; ?>" alt="<?php echo $auto['marca']; ?>" style="object-fit: contain; mix-blend-mode: multiply; padding: 20px;">
            <?php else: ?>
                <div class="placeholder-img" style="font-size: 48px;">X</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Columna Derecha: Información y Reserva -->
    <div class="detalle-info">
        <p class="breadcrumb">Catálogo / <?php echo $auto['categoria_nombre']; ?> / <?php echo $auto['marca']; ?></p>
        <h1><?php echo htmlspecialchars($auto['marca'] . ' ' . $auto['modelo'] . ' ' . $auto['año']); ?></h1>
        <p class="detalle-caracteristicas">
            <?php echo htmlspecialchars($auto['categoria_nombre']); ?> · 
            <?php echo htmlspecialchars($auto['transmision']); ?> · 
            <?php echo $auto['pasajeros']; ?> pasajeros
        </p>
        
        <div class="precio-grande">
            $<?php echo number_format($auto['precio_dia'], 2); ?> <span>/día</span>
        </div>

        <!-- Tarjeta de Selección de Fechas -->
        <div class="tarjeta-reserva">
            <h3>SELECCIONA FECHAS</h3>
            
            <!-- Formulario que envía los datos al Checkout -->
            <form action="checkout.php" method="GET">
                <input type="hidden" name="id_auto" value="<?php echo $auto['id_auto']; ?>">
                
                <div class="fechas-group">
                    <div>
                        <label>Fecha de inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div>
                        <label>Fecha de fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div class="resumen-precio">
                    <span id="dias-texto">0 días</span> × $<?php echo number_format($auto['precio_dia'], 2); ?>
                    <div class="total-estimado" id="total-texto">$0.00</div>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 15px; padding: 12px; font-size: 16px;">
                    Reservar Ahora
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Pestañas de Información Adicional -->
<div class="detalle-tabs">
        <div class="tabs-header">
        <span class="tab-btn active" onclick="abrirTab('descripcion', this)">Descripción</span>
        <span class="tab-btn" onclick="abrirTab('caracteristicas', this)">Características</span>
        <span class="tab-btn" onclick="abrirTab('politicas', this)">Políticas de Renta</span>
    </div>
    
    <!-- Contenido: Descripción -->
    <div id="descripcion" class="tab-content" style="display: block;">
        <p style="line-height: 1.6; color: #475569;"><?php echo htmlspecialchars($auto['descripcion']); ?></p>
    </div>

    <!-- Contenido: Características -->
    <div id="caracteristicas" class="tab-content" style="display: none;">
        <ul style="line-height: 1.8; color: #475569; padding-left: 20px;">
            <li><strong>Marca:</strong> <?php echo htmlspecialchars($auto['marca']); ?></li>
            <li><strong>Modelo:</strong> <?php echo htmlspecialchars($auto['modelo']); ?></li>
            <li><strong>Año:</strong> <?php echo htmlspecialchars($auto['año']); ?></li>
            <li><strong>Transmisión:</strong> <?php echo ucfirst($auto['transmision']); ?></li>
            <li><strong>Capacidad:</strong> <?php echo $auto['pasajeros']; ?> pasajeros</li>
            <li><strong>Equipamiento:</strong> Aire acondicionado, Bluetooth, Cámara de reversa, Seguros incluidos, Rines de aluminio.</li>
        </ul>
    </div>

    <!-- Contenido: Políticas -->
    <div id="politicas" class="tab-content" style="display: none;">
        <ul style="line-height: 1.8; color: #475569; padding-left: 20px;">
            <li>El conductor debe tener al menos 18 años y licencia de conducir vigente.</li>
            <li>El vehículo se entrega con el tanque lleno y debe devolverse de la misma manera.</li>
            <li>Seguro de responsabilidad civil incluido en la tarifa base.</li>
            <li>Cargo por retraso en la entrega: $200.00 MXN por hora adicional.</li>
            <li>No se permite fumar ni transportar mascotas dentro del vehículo.</li>
        </ul>
    </div>
</div>

<!-- Script para calcular el precio y validar fechas -->
<script>
    const precioDia = <?php echo $auto['precio_dia']; ?>;
    const inputInicio = document.getElementById('fecha_inicio');
    const inputFin = document.getElementById('fecha_fin');
    const textoDias = document.getElementById('dias-texto');
    const textoTotal = document.getElementById('total-texto');
    const btnReservar = document.querySelector('button[type="submit"]');

    // Función auxiliar para formatear fechas a YYYY-MM-DD
    function formatearFecha(fecha) {
        let d = new Date(fecha),
            mes = '' + (d.getMonth() + 1),
            dia = '' + d.getDate(),
            anio = d.getFullYear();

        if (mes.length < 2) mes = '0' + mes;
        if (dia.length < 2) dia = '0' + dia;

        return [anio, mes, dia].join('-');
    }

    // 1. Cuando el usuario elige la FECHA DE INICIO
    inputInicio.addEventListener('change', function() {
        if (inputInicio.value) {
            // Calcular el día siguiente a la fecha de inicio
            let fecha = new Date(inputInicio.value + 'T00:00:00');
            fecha.setDate(fecha.getDate() + 1);
            
            // Bloquear en el calendario de fin todos los días anteriores al día siguiente
            let fechaMinimaFin = formatearFecha(fecha);
            inputFin.min = fechaMinimaFin;

            // Si la fecha de fin actual es igual o menor a la de inicio, la borramos
            if (inputFin.value && inputFin.value <= inputInicio.value) {
                inputFin.value = ''; 
            }
        }
        calcularPrecio();
    });

    // 2. Cuando el usuario elige la FECHA DE FIN
    inputFin.addEventListener('change', function() {
        if (inputFin.value && inputInicio.value) {
            // Calcular el día anterior a la fecha de fin
            let fecha = new Date(inputFin.value + 'T00:00:00');
            fecha.setDate(fecha.getDate() - 1);
            
            // Bloquear en el calendario de inicio todos los días posteriores
            let fechaMaximaInicio = formatearFecha(fecha);
            inputInicio.max = fechaMaximaInicio;

            // Si la fecha de inicio es mayor o igual a la de fin, la corregimos
            if (inputInicio.value >= inputFin.value) {
                inputInicio.value = fechaMaximaInicio;
            }
        }
        calcularPrecio();
    });

        // 3. Calcular el precio total
    function calcularPrecio() {
        if (inputInicio.value && inputFin.value) {
            const fecha1 = new Date(inputInicio.value + 'T00:00:00');
            const fecha2 = new Date(inputFin.value + 'T00:00:00');
            
            // Calcular diferencia en días
            const diferenciaTiempo = fecha2 - fecha1;
            let dias = Math.round(diferenciaTiempo / (1000 * 60 * 60 * 24));
            
            // SUMAMOS 1 DÍA porque se cuenta el día de inicio
            dias = dias + 1; 

            if (dias > 0) {
                textoDias.textContent = dias + ' días';
                textoTotal.textContent = '$' + (dias * precioDia).toLocaleString('en-US', {minimumFractionDigits: 2});
                
                // Habilitar el botón de reserva
                btnReservar.disabled = false;
                btnReservar.style.opacity = '1';
                btnReservar.style.cursor = 'pointer';
            } else {
                textoDias.textContent = '0 días';
                textoTotal.textContent = '$0.00';
                
                // Deshabilitar el botón de reserva si las fechas son inválidas
                btnReservar.disabled = true;
                btnReservar.style.opacity = '0.5';
                btnReservar.style.cursor = 'not-allowed';
            }
        } else {
            // Si falta alguna fecha, deshabilitar botón
            btnReservar.disabled = true;
            btnReservar.style.opacity = '0.5';
            btnReservar.style.cursor = 'not-allowed';
        }
    }
    
    // Ejecutar una vez al cargar por si acaso
    calcularPrecio();
</script>

<script>
    function abrirTab(idTab, element) {
        // 1. Ocultar todos los contenidos
        const contenidos = document.querySelectorAll('.tab-content');
        contenidos.forEach(contenido => contenido.style.display = 'none');
        
        // 2. Quitar la clase 'active' de todos los botones
        const botones = document.querySelectorAll('.tab-btn');
        botones.forEach(boton => boton.classList.remove('active'));
        
        // 3. Mostrar el contenido seleccionado
        document.getElementById(idTab).style.display = 'block';
        
        // 4. Agregar la clase 'active' SOLO al botón que se hizo clic
        element.classList.add('active');
    }
</script>

<?php require 'includes/footer.php'; ?>