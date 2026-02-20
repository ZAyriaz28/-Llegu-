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

/* 1. OBTENER CORREO ACTUALIZADO */
$stmtUser = $db->prepare("SELECT correo FROM usuarios WHERE id = :id");
$stmtUser->execute([":id" => $usuario_id]);
$userData = $stmtUser->fetch();
$correo = $userData['correo'] ?? "estudiante@inatec.edu.ni";

/* 2. Estadísticas */
$totalClases = (int) $db->query("SELECT COUNT(DISTINCT fecha) FROM asistencias")->fetchColumn();
$stmt = $db->prepare("SELECT COUNT(*) FROM asistencias WHERE usuario_id = :id");
$stmt->execute([":id" => $usuario_id]);
$asistidas = (int) $stmt->fetchColumn();
$porcentaje = $totalClases > 0 ? round(($asistidas / $totalClases) * 100) : 0;

/* 3. Registro de hoy */
$hoy = date('Y-m-d');
$stmtCheck = $db->prepare("SELECT COUNT(*) FROM asistencias WHERE usuario_id = :id AND fecha = :fecha");
$stmtCheck->execute([":id" => $usuario_id, ":fecha" => $hoy]);
$yaRegistroHoy = ($stmtCheck->fetchColumn() > 0);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGA - Portal Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --primary-blue: #004a99;
            --tech-cyan: #00d4ff;
            --bg-gradient: radial-gradient(circle at top right, #002f61, #000b1a);
            --glass-card: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.6);
        }

        [data-theme="light"] {
            --bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --glass-card: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.5);
            --text-main: #1a2a3a;
            --text-muted: #5a6a7a;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-gradient) !important;
            background-attachment: fixed !important;
            min-height: 100vh;
            color: var(--text-main);
            margin: 0;
            padding-bottom: 100px;
            transition: all 0.4s ease;
        }

        /* Splash Screen Premium */
        #splash-screen {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: var(--bg-gradient);
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            z-index: 9999; transition: opacity 0.6s ease;
        }

        /* Header Estilo Docente */
        .header-section {
            padding: 40px 20px 80px;
            background: rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
            border-radius: 0 0 40px 40px;
        }

        .main-container {
            max-width: 500px;
            margin: -50px auto 0;
            padding: 0 15px;
        }

        .glass-card {
            background: var(--glass-card);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            padding: 22px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        /* FAB Botón de Asistencia */
        .qr-fab {
            width: 75px; height: 75px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 2rem;
            position: fixed; bottom: 35px; left: 50%;
            transform: translateX(-50%);
            z-index: 1001;
            box-shadow: 0 0 25px rgba(0, 212, 255, 0.5);
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 6px solid var(--bg-gradient);
        }

        .qr-fab:hover { transform: translateX(-50%) scale(1.1); }
        .pulse-animation { animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 212, 255, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(0, 212, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 212, 255, 0); }
        }

        /* Bottom Nav */
        .bottom-nav {
            position: fixed; bottom: 0; width: 100%;
            background: var(--glass-card);
            backdrop-filter: blur(20px);
            height: 80px;
            display: flex; justify-content: space-around; align-items: center;
            z-index: 1000;
            border-top: 1px solid var(--glass-border);
            padding-bottom: 10px;
        }

        .nav-item { color: var(--text-muted); text-align: center; cursor: pointer; transition: 0.3s; }
        .nav-item.active { color: var(--tech-cyan); text-shadow: 0 0 10px var(--tech-cyan); }

        /* Progress Bar Tech */
        .progress-tech {
            height: 10px; background: rgba(255,255,255,0.1); border-radius: 20px; overflow: hidden;
        }
        .progress-bar-tech {
            background: linear-gradient(90deg, var(--primary-blue), var(--tech-cyan));
            box-shadow: 0 0 10px var(--tech-cyan);
        }

        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeInUp 0.5s ease; }

        /* Dark/Light Switcher */
        .btn-theme-toggle {
            background: var(--glass-card);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
            width: 45px; height: 45px; border-radius: 50%;
        }

        [data-theme="light"] .glass-card { box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    </style>
</head>
<body data-theme="dark">

    <div id="splash-screen">
        <div class="spinner-border text-info mb-3" style="width: 3rem; height: 3rem;"></div>
        <h5 class="fw-bold animate__animated animate__pulse animate__infinite">SGA TECH</h5>
        <p class="small text-info opacity-75">Sincronizando terminal...</p>
    </div>

    <div class="header-section">
        <div class="d-flex justify-content-between align-items-center container" style="max-width: 500px;">
            <div>
                <h4 class="fw-bold mb-0">¡Hola, <?= explode(' ', trim($nombre))[0] ?>! 👋</h4>
                <p class="small text-info mb-0 fw-medium">ESTUDIANTE ACTIVO</p>
            </div>
            <button class="btn btn-theme-toggle" id="theme-toggle">
                <i class="bi bi-moon-stars"></i>
            </button>
        </div>
    </div>

    <div class="main-container">
        <div id="tab-home" class="tab-content active">
            <div class="glass-card mt-2">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-muted small fw-bold mb-0">PROGRESO ACADÉMICO</h6>
                    <span class="badge rounded-pill bg-info text-dark"><?= $porcentaje ?>%</span>
                </div>
                <div class="progress-tech">
                    <div class="progress-bar-tech h-100" style="width: <?= $porcentaje ?>%"></div>
                </div>
            </div>

            <h6 class="fw-bold mb-3 ms-2 small text-uppercase" style="letter-spacing: 1px;">Terminal de Clase</h6>
            <div class="glass-card border-start border-info border-4 animate__animated animate__fadeInLeft">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="bi bi-shield-lock text-info fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Ciberseguridad y Redes</h5>
                        <p class="small text-muted mb-0"><i class="bi bi-geo-alt me-1"></i> Laboratorio A1 - Somoto</p>
                    </div>
                </div>
            </div>

            <div class="glass-card bg-opacity-10" style="background: rgba(0, 212, 255, 0.03);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small fw-medium">Token de sesión:</span>
                    <span class="badge bg-dark text-info font-monospace">SGA-<?= date('Hi') ?>-EST</span>
                </div>
            </div>
        </div>

        <div id="tab-horario" class="tab-content">
            <h5 class="fw-bold mb-4">Mi Horario</h5>
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                    <div>
                        <p class="small text-muted mb-0">Lunes</p>
                        <p class="fw-bold mb-0">Ciberseguridad</p>
                    </div>
                    <span class="text-info fw-bold">08:00 AM</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small text-muted mb-0">Martes</p>
                        <p class="fw-bold mb-0">Diseño Web Moderno</p>
                    </div>
                    <span class="text-info fw-bold">10:30 AM</span>
                </div>
            </div>
        </div>
    </div>

    <div class="qr-fab <?= $yaRegistroHoy ? 'bg-success pulse-animation' : 'pulse-animation' ?>" 
         id="btnAsistenciaCheck"
         <?= $yaRegistroHoy ? '' : 'onclick="confirmarFinalizado()"' ?>
         style="background: <?= $yaRegistroHoy ? '#198754' : 'linear-gradient(135deg, var(--primary-blue), var(--tech-cyan))' ?>;">
        <i class="bi <?= $yaRegistroHoy ? 'bi-patch-check-fill' : 'bi-qr-code-scan' ?>"></i>
    </div>

    <div class="bottom-nav">
        <div class="nav-item active" onclick="changeTab('home', this)">
            <i class="bi bi-grid-1x2-fill fs-4"></i>
            <div class="small" style="font-size: 0.7rem;">Inicio</div>
        </div>
        <div class="nav-item" onclick="changeTab('horario', this)">
            <i class="bi bi-calendar3 fs-4"></i>
            <div class="small" style="font-size: 0.7rem;">Horario</div>
        </div>
        <div style="flex: 0.5;"></div> <div class="nav-item" data-bs-toggle="offcanvas" data-bs-target="#panelPerfil">
            <i class="bi bi-person-bounding-box fs-4"></i>
            <div class="small" style="font-size: 0.7rem;">Perfil</div>
        </div>
        <div class="nav-item" onclick="window.location.href='logout.php'">
            <i class="bi bi-power fs-4"></i>
            <div class="small" style="font-size: 0.7rem;">Salir</div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="panelPerfil" style="background: var(--bg-gradient); color: var(--text-main); width: 85%;">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title fw-bold">Configuración Perfil</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <div class="text-center mb-4 mt-2">
                <div class="d-inline-block position-relative">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=00d4ff&color=000&size=128" class="rounded-circle shadow-lg border border-3 border-info" width="100">
                    <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-2"></span>
                </div>
                <h5 class="mt-3 fw-bold mb-0 text-white"><?= htmlspecialchars($nombre) ?></h5>
                <p class="text-info small mb-0 font-monospace">ID: EST-<?= str_pad($usuario_id, 4, '0', STR_PAD_LEFT) ?></p>
            </div>

            <form id="formPerfil" class="glass-card">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">CORREO INSTITUCIONAL</label>
                    <input type="email" class="form-control bg-dark border-secondary text-white-50 rounded-3" value="<?= htmlspecialchars($correo) ?>" readonly>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">ACTUALIZAR LLAVE DE ACCESO</label>
                    <input type="password" class="form-control bg-dark border-secondary text-white rounded-3" name="password" placeholder="Nueva contraseña">
                </div>
                <button type="submit" class="btn btn-info w-100 rounded-pill py-2 fw-bold text-dark">GUARDAR CAMBIOS</button>
            </form>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
        <div id="toastAsistencia" class="toast align-items-center text-white bg-dark border-info rounded-4" role="alert">
            <div class="d-flex p-2">
                <div class="toast-body fw-medium"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Splash Screen Logic
        window.addEventListener("load", () => {
            setTimeout(() => {
                const splash = document.getElementById("splash-screen");
                splash.style.opacity = "0";
                setTimeout(() => splash.style.display = "none", 600);
            }, 1000);
        });

        // Theme Toggle (Dark/Light)
        const themeBtn = document.getElementById('theme-toggle');
        const body = document.body;
        themeBtn.addEventListener('click', () => {
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            body.setAttribute('data-theme', newTheme);
            themeBtn.innerHTML = newTheme === 'dark' ? '<i class="bi bi-moon-stars"></i>' : '<i class="bi bi-sun"></i>';
        });

        // Tabs Logic
        function changeTab(tabId, element) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            document.getElementById('tab-' + tabId).classList.add('active');
            element.classList.add('active');
        }

        // Registrar Asistencia
        function confirmarFinalizado() {
            const formData = new FormData();
            formData.append("clase", "Ciberseguridad y Redes");

            fetch("registrar_asistencia.php", { method: "POST", body: formData })
            .then(res => res.json())
            .then(data => {
                const toastEl = document.getElementById('toastAsistencia');
                const toastBody = toastEl.querySelector('.toast-body');
                
                if (data.status === "ok") {
                    toastBody.innerHTML = "✨ ¡Perfecto! Asistencia confirmada.";
                    const btn = document.getElementById("btnAsistenciaCheck");
                    btn.classList.replace("pulse-animation", "pulse-animation");
                    btn.style.background = "#198754";
                    btn.innerHTML = '<i class="bi bi-patch-check-fill"></i>';
                    btn.onclick = null;
                } else {
                    toastBody.innerHTML = "⚠️ " + (data.message || "Error de validación.");
                }
                new bootstrap.Toast(toastEl).show();
            });
        }
    </script>
</body>
</html>
