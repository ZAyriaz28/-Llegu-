<?php
require_once "config/db.php";
require_once "config/auth.php";

/* Validar rol */
if (($_SESSION["rol"] ?? "") !== "estudiante") {
    header("Location: /index.php");
    exit();
}

$usuario_id = (int) $_SESSION["id"];
$nombre     = $_SESSION["nombre"];

/* 1. Obtener total de clases */
$totalClases = (int) $db
    ->query("SELECT COUNT(DISTINCT fecha) FROM asistencias")
    ->fetchColumn();

/* 2. Total asistencias del estudiante */
$stmt = $db->prepare("SELECT COUNT(*) FROM asistencias WHERE usuario_id = :id");
$stmt->execute([":id" => $usuario_id]);
$asistidas = (int) $stmt->fetchColumn();

/* 3. Calcular porcentaje */
$porcentaje = $totalClases > 0 ? round(($asistidas / $totalClases) * 100) : 0;

/* 4. VERIFICAR SI YA ASISTIÓ HOY (Esto corrige el error) */
$hoy = date('Y-m-d');
$stmtCheck = $db->prepare("SELECT COUNT(*) FROM asistencias WHERE usuario_id = :id AND fecha = :fecha");
$stmtCheck->execute([
    ":id"    => $usuario_id, 
    ":fecha" => $hoy
]);
$yaRegistroHoy = ($stmtCheck->fetchColumn() > 0);

?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Estudiante - INATEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary: #0061ff;
            --accent: #60efff;
            --bg-light: #f4f7fe;
            --card-bg: #ffffff;
            --text-main: #1b2559;
        }

        body.dark-mode {
            --bg-light: #0b1437;
            --card-bg: #111c44;
            --text-main: #ffffff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            overflow-x: hidden;
            transition: background 0.3s ease;
        }

        /* Pantalla de Carga (Splash Screen) */
        #splash-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            color: white;
            transition: opacity 0.5s ease, visibility 0.5s;
        }

        /* Header de Pantalla Completa */
        .header-gradient {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            height: 220px;
            border-radius: 0 0 40px 40px;
            padding: 40px 25px;
            color: white;
        }

        .main-content {
            max-width: 600px;
            /* Un poco más ancho para mejor lectura */
            margin: -60px auto 0;
            padding: 0 20px 100px;
        }

        .glass-card {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            color: var(--text-main);
        }

        /* Botón QR Flotante */
        .qr-fab {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            position: fixed;
            bottom: 35px;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid var(--bg-light);
            z-index: 1001;
            box-shadow: 0 10px 20px rgba(0, 97, 255, 0.3);
            cursor: pointer;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: var(--card-bg);
            height: 75px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.05);
        }

        .nav-item {
            color: #a3aed0;
            text-align: center;
            flex: 1;
            cursor: pointer;
        }

        .nav-item.active {
            color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .qr-fab.bg-success {
    animation: scaleIn 0.3s ease-in-out;
}

@keyframes scaleIn {
    from { transform: translateX(-50%) scale(0); }
    to { transform: translateX(-50%) scale(1); }
}
    </style>
</head>

<body>

    <div id="splash-screen">
        <div class="spinner-border mb-3" role="status"></div>
        <h4 class="fw-bold">INATEC Somoto</h4>
        <p class="small opacity-75">Cargando tu portal...</p>
    </div>

    <div class="header-gradient">
        <div class="d-flex justify-content-between align-items-center max-width-container mx-auto"
            style="max-width: 600px;">
            <div>
                <!-- 🔹 Nombre dinámico -->
                <h4 class="fw-bold mb-0">¡Hola, <span id="userName"><?= htmlspecialchars($nombre) ?></span>! 👋</h4>
                <p class="small opacity-75 mb-0">Estudiante Técnico</p>
            </div>
            <button class="btn text-white p-0" id="btnDarkMode">
                <i class="bi bi-moon-stars fs-3"></i>
            </button>
        </div>
    </div>

    <div class="main-content">
        <div id="tab-home" class="tab-content active">
            <div class="glass-card mb-4 text-center">
                <h6 class="text-muted small fw-bold">PROGRESO DE ASISTENCIA</h6>

                <!-- 🔹 Porcentaje dinámico -->
                <h2 class="fw-bold text-primary"><?= $porcentaje ?>%</h2>

                <div class="progress" style="height: 8px;">
                    <!-- 🔹 Barra ahora usa el porcentaje real -->
                    <div class="progress-bar" style="width: <?= $porcentaje ?>%"></div>
                </div>
            </div>

            <h6 class="fw-bold mb-3 ms-2">TU CLASE DE HOY</h6>
            <div class="glass-card border-start border-primary border-4">
                <h5 class="fw-bold mb-1">Ciberseguridad y Redes</h5>
                <p class="small text-muted mb-0">
                    <i class="bi bi-geo-alt-fill text-primary"></i>
                    Centro Tecnológico - Somoto
                </p>
            </div>
        </div>

        <div id="tab-horario" class="tab-content">
            <h5 class="fw-bold mb-3">Horario Semanal</h5>
            <div class="glass-card">
                <div class="d-flex justify-content-between">
                    <span><strong>Lunes:</strong> Diseño Web</span>
                    <span class="text-primary">08:00 AM</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 🔥 BOTÓN INTELIGENTE -->
    <div 
        class="qr-fab <?= $yaRegistroHoy ? 'bg-success border-success' : '' ?>" 
        id="btnAsistenciaCheck"
        <?= $yaRegistroHoy ? '' : 'onclick="confirmarFinalizado()"' ?>
        style="background: linear-gradient(135deg, var(--primary), var(--accent)); border: 5px solid var(--bs-primary);">

        <i class="bi <?= $yaRegistroHoy ? 'bi-check2-circle' : 'bi-check-lg' ?>"></i>
    </div>

    <div class="bottom-nav">
        <div class="nav-item active" onclick="changeTab('home', this)">
            <i class="bi bi-house-door-fill fs-4"></i>
            <div style="font-size: 0.65rem;">Inicio</div>
        </div>

        <div class="nav-item" onclick="changeTab('horario', this)">
            <i class="bi bi-calendar-week fs-4"></i>
            <div style="font-size: 0.65rem;">Horario</div>
        </div>

        <div style="flex: 1;"></div>

        <div class="nav-item" data-bs-toggle="offcanvas" data-bs-target="#panelPerfil">
            <i class="bi bi-person-circle fs-4"></i>
            <div style="font-size: 0.65rem;">Perfil</div>
        </div>

        <!-- 🔹 CERRAR SESIÓN CORRECTO -->
        <div class="nav-item" onclick="window.location.href='logout.php'">
            <i class="bi bi-box-arrow-right fs-4"></i>
            <div style="font-size: 0.65rem;">Salir</div>
        </div>
    </div>

    <script>

// 🔹 Splash Screen Auto Hide
window.addEventListener("load", function () {
    const splash = document.getElementById("splash-screen");

    if (splash) {
        setTimeout(() => {
            splash.style.opacity = "0";
            splash.style.transition = "opacity 0.5s ease";

            setTimeout(() => {
                splash.style.display = "none";
            }, 500);
        }, 800);
    }
});

function confirmarFinalizado() {

    fetch("registrar_asistencia.php")
    .then(response => response.json())
    .then(data => {

        const toastElement = document.getElementById('toastAsistencia');
        const toastBody = toastElement.querySelector('.toast-body');

        if (data.status === "ok") {

            toastBody.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Asistencia registrada correctamente.';

            const btn = document.getElementById("btnAsistenciaCheck");
            btn.classList.add("bg-success");
            btn.innerHTML = '<i class="bi bi-check2-circle"></i>';
            btn.onclick = null;

        } else if (data.status === "existe") {

            toastBody.innerHTML = '<i class="bi bi-info-circle-fill me-2"></i> Ya registraste asistencia hoy.';

        } else {

            toastBody.innerHTML = '<i class="bi bi-x-circle-fill me-2"></i> Error al registrar asistencia.';

        }

        const toast = new bootstrap.Toast(toastElement, { delay: 2000 });
        toast.show();

    })
    .catch(error => {
        console.error(error);
    });

}

</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="modo-oscuro.js"></script>
    <script src="Geolocalizacion-API.js"></script>
    <script src="guardarCambiosPerfil.js"></script>

    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 2000;">
    <div id="toastAsistencia" class="toast align-items-center text-white bg-primary border-0 rounded-4 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-info-circle-fill me-2"></i> Asistencia ya registrada correctamente.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
</body>

</html>
