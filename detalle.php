<?php 
require 'config/conexion.php';
date_default_timezone_set('America/Mexico_City');

// Validar que venga un ID por la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: catalogo.php");
    exit;
}

$id_auto = (int)$_GET['id'];

// Consultar el auto específico
$sql = "SELECT a.*, c.nombre as categoria_nombre 
        FROM AUTOMOVILES a 
        INNER JOIN CATEGORIAS c ON a.id_categoria = c.id_categoria 
        WHERE a.id_auto = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_auto]);
$auto = $stmt->fetch();

// Si el auto no existe o está en mantenimiento, regresar al catálogo
if (!$auto || $auto['estado'] === 'mantenimiento') {
    header("Location: catalogo.php");
    exit;
}

// Fechas ya reservadas de este auto (confirmadas o pendientes que no han terminado).
// Se usan para bloquear esos días en el calendario.
$stmt = $pdo->prepare("SELECT fecha_inicio AS `from`, fecha_final AS `to`
                       FROM RESERVACIONES
                       WHERE id_auto = :id
                       AND estado IN ('confirmada', 'pendiente')
                       AND fecha_final >= :hoy
                       ORDER BY fecha_inicio ASC");
$stmt->execute([':id' => $id_auto, ':hoy' => date('Y-m-d')]);
$ocupadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hasta aquí no se ha enviado nada al navegador, así que ya se puede incluir el header
require 'includes/header.php'; 
?>

<!-- Calendario (Flatpickr) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Estilo AutoRent para el calendario.
     Va DESPUÉS del CSS de Flatpickr y con selectores más específicos para que sí se aplique. -->
<style>
    body .flatpickr-calendar {
        font-family: Arial, sans-serif;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
    }

    /* Encabezado: mes (lista desplegable) y año */
    body .flatpickr-calendar .flatpickr-months .flatpickr-month { height: 42px; color: #1e293b; fill: #1e293b; }
    body .flatpickr-calendar .flatpickr-current-month {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding-top: 8px;
        font-size: 15px;
        font-weight: bold;
    }
    body .flatpickr-calendar .flatpickr-current-month .flatpickr-monthDropdown-months {
        font-size: 15px;
        font-weight: bold;
        color: #1e293b;
        padding: 2px 6px;
        border-radius: 4px;
        cursor: pointer;
    }
    body .flatpickr-calendar .flatpickr-current-month .flatpickr-monthDropdown-months:hover { background: #f1f5f9; }
    body .flatpickr-calendar .flatpickr-current-month input.cur-year { font-size: 15px; font-weight: bold; color: #1e293b; }

    /* Flechas de mes anterior / siguiente */
    body .flatpickr-calendar .flatpickr-months .flatpickr-prev-month,
    body .flatpickr-calendar .flatpickr-months .flatpickr-next-month { padding: 10px; color: #475569; fill: #475569; }
    body .flatpickr-calendar .flatpickr-months .flatpickr-prev-month svg,
    body .flatpickr-calendar .flatpickr-months .flatpickr-next-month svg { fill: #475569; }
    body .flatpickr-calendar .flatpickr-months .flatpickr-prev-month:hover svg,
    body .flatpickr-calendar .flatpickr-months .flatpickr-next-month:hover svg { fill: #1e3a8a; }

    /* Días de la semana */
    body .flatpickr-calendar span.flatpickr-weekday { color: #64748b; font-size: 12px; font-weight: bold; }

    /* Días */
    body .flatpickr-calendar .flatpickr-day { color: #1e293b; border-radius: 6px; }
    body .flatpickr-calendar .flatpickr-day:hover { background: #f1f5f9; border-color: #f1f5f9; }
    body .flatpickr-calendar .flatpickr-day.today { border-color: #1e3a8a; }
    body .flatpickr-calendar .flatpickr-day.today:hover { background: #f1f5f9; border-color: #1e3a8a; color: #1e293b; }
    body .flatpickr-calendar .flatpickr-day.selected,
    body .flatpickr-calendar .flatpickr-day.selected:hover {
        background: #1e3a8a;
        border-color: #1e3a8a;
        color: #ffffff;
    }
    body .flatpickr-calendar .flatpickr-day.prevMonthDay,
    body .flatpickr-calendar .flatpickr-day.nextMonthDay { color: #94a3b8; }

    /* Días no disponibles (pasados o fuera de rango) */
    body .flatpickr-calendar .flatpickr-day.flatpickr-disabled,
    body .flatpickr-calendar .flatpickr-day.flatpickr-disabled:hover { color: #cbd5e1; background: transparent; cursor: not-allowed; }

    /* Días ya reservados por otro cliente */
    body .flatpickr-calendar .flatpickr-day.dia-reservado,
    body .flatpickr-calendar .flatpickr-day.dia-reservado:hover {
        background: #fee2e2;
        border-color: #fee2e2;
        color: #991b1b;
        text-decoration: line-through;
        cursor: not-allowed;
    }
</style>

<div class="detalle-container">
        <!-- Columna Izquierda: Galería de Fotos -->
    <div class="detalle-galeria">
        <div class="foto-principal">
            <?php if(!empty($auto['imagen']) && file_exists('assets/img/' . $auto['imagen'])): ?>
                <!-- Usamos object-fit contain para que la imagen no se corte -->
                <img src="assets/img/<?php echo $auto['imagen']; ?>" alt="<?php echo htmlspecialchars($auto['marca']); ?>" style="object-fit: contain; mix-blend-mode: multiply; padding: 20px;">
            <?php else: ?>
                <div class="placeholder-img" style="font-size: 48px;">X</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Columna Derecha: Información y Reserva -->
    <div class="detalle-info">
        <p class="breadcrumb">Catálogo / <?php echo htmlspecialchars($auto['categoria_nombre']); ?> / <?php echo htmlspecialchars($auto['marca']); ?></p>
        <h1><?php echo htmlspecialchars($auto['marca'] . ' ' . $auto['modelo'] . ' ' . $auto['año']); ?></h1>
        <p class="detalle-caracteristicas">
            <?php echo htmlspecialchars($auto['categoria_nombre']); ?> · 
            <?php echo htmlspecialchars($auto['transmision']); ?> · 
            <?php echo $auto['pasajeros']; ?> pasajeros
        </p>
        
        <div class="precio-grande">
            $<?php echo number_format($auto['precio_dia'], 2); ?> <span>/día</span>
        </div>

        <?php if (($_GET['error'] ?? '') === 'ocupado'): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 6px; margin-bottom: 15px; font-size: 14px;">
                Ese auto ya fue reservado en las fechas que elegiste. Por favor selecciona otras fechas.
            </div>
        <?php endif; ?>

        <!-- Tarjeta de Selección de Fechas -->
        <div class="tarjeta-reserva">
            <h3>SELECCIONA FECHAS</h3>
            
            <!-- Formulario que envía los datos al Checkout -->
            <form action="checkout.php" method="GET">
                <input type="hidden" name="id_auto" value="<?php echo $auto['id_auto']; ?>">
                
                <div class="fechas-group">
                    <div>
                        <label>Fecha de inicio</label>
                        <input type="text" name="fecha_inicio" id="fecha_inicio" placeholder="Selecciona" required readonly style="background: white; cursor: pointer;">
                    </div>
                    <div>
                        <label>Fecha de fin</label>
                        <input type="text" name="fecha_fin" id="fecha_fin" placeholder="Selecciona" required readonly style="background: white; cursor: pointer;">
                    </div>
                </div>

                <?php if (count($ocupadas) > 0): ?>
                    <p style="font-size: 12px; color: #64748b; margin-bottom: 15px; line-height: 1.5;">
                        <strong>Fechas no disponibles:</strong>
                        <?php
                            $rangos = [];
                            foreach ($ocupadas as $o) {
                                $rangos[] = date('d/m/Y', strtotime($o['from'])) . ' al ' . date('d/m/Y', strtotime($o['to']));
                            }
                            echo implode(' · ', $rangos);
                        ?>
                    </p>
                <?php endif; ?>

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

<!-- Flatpickr: calendario que permite bloquear fechas -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<!-- Script para el calendario, el precio y la validación de fechas -->
<script>
    const precioDia = <?php echo $auto['precio_dia']; ?>;
    const hoy = '<?php echo date('Y-m-d'); ?>';
    // Reservas existentes de este auto, ordenadas por fecha: [{from:'2026-11-20', to:'2026-11-25'}, ...]
    const ocupadas = <?php echo json_encode($ocupadas); ?>;

    const inputInicio = document.getElementById('fecha_inicio');
    const inputFin = document.getElementById('fecha_fin');
    const textoDias = document.getElementById('dias-texto');
    const textoTotal = document.getElementById('total-texto');
    const btnReservar = document.querySelector('.tarjeta-reserva button[type="submit"]');

    // Suma (o resta) días a una fecha 'YYYY-MM-DD' y la regresa en el mismo formato
    function sumarDias(fechaStr, n) {
        const d = new Date(fechaStr + 'T00:00:00');
        d.setDate(d.getDate() + n);
        const mes = String(d.getMonth() + 1).padStart(2, '0');
        const dia = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + mes + '-' + dia;
    }

    // Tope "sin límite" para maxDate (no se usa null porque puede romper la navegación del calendario)
    const SIN_LIMITE = '2100-12-31';

    // Convierte un objeto Date a 'YYYY-MM-DD'
    function fechaStr(d) {
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    // La renta mínima es de 2 días (fin > inicio), así que también se bloquean:
    //  - como INICIO: el día anterior a una reserva (no quedaría ningún día de fin posible)
    //  - como FIN: el día posterior a una reserva (no quedaría ningún día de inicio posible)
    const bloqueosInicio = ocupadas.map(r => ({ from: sumarDias(r.from, -1), to: r.to }));
    const bloqueosFin    = ocupadas.map(r => ({ from: r.from, to: sumarDias(r.to, 1) }));

    const configBase = {
        locale: 'es',
        dateFormat: 'Y-m-d',
        minDate: hoy,
        maxDate: SIN_LIMITE,
        monthSelectorType: 'dropdown',   // el mes se elige en una lista desplegable
        disableMobile: true,             // usa este calendario también en celular (el nativo no bloquea días)
        // Marca en rojo los días que ya están reservados
        onDayCreate: function(dObj, dStr, fp, dayElem) {
            const f = fechaStr(dayElem.dateObj);
            if (ocupadas.some(r => f >= r.from && f <= r.to)) {
                dayElem.classList.add('dia-reservado');
                dayElem.title = 'Reservado';
            }
        }
    };

    const calInicio = flatpickr(inputInicio, Object.assign({}, configBase, {
        disable: bloqueosInicio,
        onChange: function(fechas, valor) {
            if (!valor) {
                // Se borró el inicio: el fin vuelve a tener su rango completo
                calFin.set('minDate', hoy);
                calFin.set('maxDate', SIN_LIMITE);
            }
            if (valor) {
                // El fin debe ser al menos un día después del inicio...
                calFin.set('minDate', sumarDias(valor, 1));

                // ...y antes de la siguiente reserva, para no encimarse con ella
                const siguiente = ocupadas.find(r => r.from > valor);
                calFin.set('maxDate', siguiente ? sumarDias(siguiente.from, -1) : SIN_LIMITE);

                // Si el fin que ya estaba elegido dejó de ser válido, se borra
                const fin = inputFin.value;
                if (fin && (fin <= valor || (siguiente && fin >= siguiente.from))) {
                    calFin.clear();
                }
            }
            calcularPrecio();
        }
    }));

    const calFin = flatpickr(inputFin, Object.assign({}, configBase, {
        disable: bloqueosFin,
        onChange: function(fechas, valor) {
            if (!valor) {
                // Se borró el fin: el inicio vuelve a tener su rango completo
                calInicio.set('minDate', hoy);
                calInicio.set('maxDate', SIN_LIMITE);
            }
            if (valor) {
                // El inicio debe quedar después de la reserva anterior...
                const anterior = ocupadas.filter(r => r.to < valor).pop();
                const minimo = anterior ? sumarDias(anterior.to, 1) : hoy;
                calInicio.set('minDate', minimo > hoy ? minimo : hoy);

                // ...y antes de la fecha de fin
                calInicio.set('maxDate', sumarDias(valor, -1));

                // Si el inicio que ya estaba elegido dejó de ser válido, se borra
                const inicio = inputInicio.value;
                if (inicio && (inicio >= valor || inicio < minimo)) {
                    calInicio.clear();
                }
            }
            calcularPrecio();
        }
    }));

    // Calcular el precio total
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
            textoDias.textContent = '0 días';
            textoTotal.textContent = '$0.00';

            // Si falta alguna fecha, deshabilitar botón
            btnReservar.disabled = true;
            btnReservar.style.opacity = '0.5';
            btnReservar.style.cursor = 'not-allowed';
        }
    }
    
    // Ejecutar una vez al cargar
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