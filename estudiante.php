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

/* 2. ESTADÍSTICAS DE ASISTENCIA */
$totalClases = (int) $db->query("SELECT COUNT(DISTINCT fecha) FROM asistencias")->fetchColumn();
$stmt = $db->prepare("SELECT COUNT(*) FROM asistencias WHERE usuario_id = :id");
$stmt->execute([":id" => $usuario_id]);
$asistidas = (int) $stmt->fetchColumn();
$porcentaje = $totalClases > 0 ? round(($asistidas / $totalClases) * 100) : 0;

/* 3. REGISTRO DE HOY */
$hoy = date('Y-m-d');
$stmtCheck = $db->prepare("SELECT COUNT(*) FROM asistencias WHERE usuario_id = :id AND fecha = :fecha");
$stmtCheck->execute([":id" => $usuario_id, ":fecha" => $hoy]);
$yaRegistroHoy = ($stmtCheck->fetchColumn() > 0);

/* 4. OBTENER HISTORIAL (BITÁCORA) */
$stmtHistorial = $db->prepare("
    SELECT f.fecha, 
           CASE WHEN a.id IS NOT NULL THEN 'Presente' ELSE 'Ausente' END AS estado
    FROM (SELECT DISTINCT fecha FROM asistencias) f
    LEFT JOIN asistencias a ON f.fecha = a.fecha AND a.usuario_id = :id
    ORDER BY f.fecha DESC LIMIT 8
");
$stmtHistorial->execute([":id" => $usuario_id]);
$historial = $stmtHistorial->fetchAll();

/* Formato de ID para QR */
$estudiante_id_format = "EST-" . str_pad($usuario_id, 5, '0', STR_PAD_LEFT);
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

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
            overflow-x: hidden;
        }

        /* SOLUCIÓN AL EFECTO BORROSO */
        .tab-content { 
            display: none; 
            backface-visibility: hidden; 
            -webkit-font-smoothing: antialiased;
            transform: translateZ(0); 
        }
        .tab-content.active { 
            display: block; 
            animation: fadeInUp 0.4s ease-out; 
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
            transform: translateZ(0); /* Evita desenfoque */
        }

        .header-section {
            padding: 40px 20px 80px;
            background: rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
            border-radius: 0 0 40px 40px;
        }

        .main-container { max-width: 500px; margin: -50px auto 0; padding: 0 15px; }

        .qr-fab {
            width: 75px; height: 75px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 2rem;
            position: fixed; bottom: 35px; left: 50%; transform: translateX(-50%);
            z-index: 1001; border: 6px solid var(--bg-gradient);
            cursor: pointer; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .pulse-animation { animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 212, 255, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(0, 212, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 212, 255, 0); }
        }

        .bottom-nav {
            position: fixed; bottom: 0; width: 100%; height: 80px;
            background: var(--glass-card); backdrop-filter: blur(20px);
            display: flex; justify-content: space-around; align-items: center;
            z-index: 1000; border-top: 1px solid var(--glass-border);
        }
        .nav-item { color: var(--text-muted); text-align: center; cursor: pointer; transition: 0.3s; flex: 1; }
        .nav-item.active { color: var(--tech-cyan); text-shadow: 0 0 10px var(--tech-cyan); }

        .badge-presente { background: rgba(0, 255, 128, 0.1) !important; color: #00ff80 !important; border: 1px solid rgba(0, 255, 128, 0.3); }
        .badge-ausente { background: rgba(255, 71, 87, 0.1) !important; color: #ff4757 !important; border: 1px solid rgba(255, 71, 87, 0.3); }

        #qrcodeEstudiante img {
            margin: auto;
            border: 10px solid white;
            border-radius: 15px;
        }

        .progress-tech { height: 10px; background: rgba(255,255,255,0.1); border-radius: 20px; overflow: hidden; }
        .progress-bar-tech { background: linear-gradient(90deg, var(--primary-blue), var(--tech-cyan)); }
    </style>
</head>
<body data-theme="dark">

    <div id="splash-screen" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: var(--bg-gradient); display: flex; flex-direction: column; justify-content: center; align-items: center; z-index: 9999; transition: opacity 0.6s ease;">
        <div class="spinner-border text-info mb-3"></div>
        <h5 class="fw-bold animate__animated animate__pulse animate__infinite">SGA TECH</h5>
    </div>

    <div class="header-section">
        <div class="d-flex justify-content-between align-items-center container" style="max-width: 500px;">
            <div>
                <h4 class="fw-bold mb-0">¡Hola, <?= explode(' ', trim($nombre))[0] ?>! 👋</h4>
                <p class="small text-info mb-0 fw-medium">ESTUDIANTE ACTIVO</p>
            </div>
            <button class="btn border-0 text-white" id="theme-toggle">
                <i class="bi bi-moon-stars fs-5"></i>
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

            <div class="glass-card border-start border-info border-4">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="bi bi-shield-lock text-info fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Ciberseguridad y Redes</h5>
                        <p class="small text-muted mb-0">Laboratorio A1 - Somoto</p>
                    </div>
                </div>
            </div>

            <div class="glass-card bg-opacity-10 text-center" style="border: 1px dashed var(--tech-cyan);">
                <p class="small text-muted mb-2 text-uppercase fw-bold" style="letter-spacing: 2px;">Llave de Acceso Dinámica</p>
                <h4 class="font-monospace text-info mb-0" style="letter-spacing: 4px;">
                    <?= date('H') ?>•<?= substr(md5($usuario_id), 0, 4) ?>•<?= date('i') ?>
                </h4>
            </div>
        </div>

        <div id="tab-reportes" class="tab-content">
            <h5 class="fw-bold mb-4">Registro de Bitácora</h5>
            <?php foreach ($historial as $h): ?>
                <div class="glass-card py-3 mb-2 d-flex justify-content-between align-items-center animate__animated animate__fadeInUp">
                    <div>
                        <p class="mb-0 fw-bold small"><?= date('d M, Y', strtotime($h['fecha'])) ?></p>
                        <p class="mb-0 text-muted small">Módulo Técnico</p>
                    </div>
                    <span class="badge rounded-pill <?= $h['estado'] == 'Presente' ? 'badge-presente' : 'badge-ausente' ?> px-3">
                        <?= $h['estado'] ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="tab-horario" class="tab-content">
            <h5 class="fw-bold mb-4">Mi Horario</h5>
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                    <div><p class="small text-muted mb-0">Lunes</p><p class="fw-bold mb-0">Ciberseguridad</p></div>
                    <span class="text-info fw-bold">08:00 AM</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div><p class="small text-muted mb-0">Martes</p><p class="fw-bold mb-0">Diseño Web Moderno</p></div>
                    <span class="text-info fw-bold">10:30 AM</span>
                </div>
            </div>
        </div>

        <div id="tab-carnet" class="tab-content">
            <div class="text-center">
                <h5 class="fw-bold mb-4">Credencial Digital</h5>
                <div class="glass-card p-4">
                    <div id="qrcodeEstudiante" class="mb-3"></div>
                    <h5 class="fw-bold mb-0"><?= $nombre ?></h5>
                    <p class="text-info font-monospace small"><?= $estudiante_id_format ?></p>
                    <hr class="opacity-25">
                    <p class="small text-muted">Usa este código para identificarte en el centro.</p>
                </div>
            </div>
        </div>

    </div>

    <div class="qr-fab <?= $yaRegistroHoy ? 'bg-success' : 'pulse-animation' ?>" 
         id="btnAsistenciaCheck"
         <?= $yaRegistroHoy ? '' : 'onclick="confirmarFinalizado()"' ?>
         style="background: <?= $yaRegistroHoy ? '#198754' : 'linear-gradient(135deg, var(--primary-blue), var(--tech-cyan))' ?>;">
        <i class="bi <?= $yaRegistroHoy ? 'bi-patch-check-fill' : 'bi-qr-code-scan' ?>"></i>
    </div>

    <div class="bottom-nav">
        <div class="nav-item active" onclick="changeTab('home', this)">
            <i class="bi bi-cpu fs-4"></i><div class="small" style="font-size: 0.65rem;">Core</div>
        </div>
        <div class="nav-item" onclick="changeTab('reportes', this)">
            <i class="bi bi-journal-text fs-4"></i><div class="small" style="font-size: 0.65rem;">Bitácora</div>
        </div>
        <div style="flex: 0.4;"></div>
        <div class="nav-item" onclick="changeTab('carnet', this); generarQRIdentidad();">
            <i class="bi bi-qr-code-scan fs-4"></i><div class="small" style="font-size: 0.65rem;">ID-Tech</div>
        </div>
        <div class="nav-item" data-bs-toggle="offcanvas" data-bs-target="#panelPerfil">
            <i class="bi bi-person-gear fs-4"></i><div class="small" style="font-size: 0.65rem;">Perfil</div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="panelPerfil" style="background: var(--bg-gradient); color: var(--text-main); width: 85%;">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title fw-bold">Configuración</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <div class="text-center mb-4">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=00d4ff&color=000" class="rounded-circle border border-3 border-info mb-3" width="90">
                <h6 class="fw-bold mb-0"><?= $nombre ?></h6>
                <button class="btn btn-link btn-sm text-danger text-decoration-none mt-2" onclick="window.location.href='logout.php'">Cerrar Sesión</button>
            </div>
            <form id="formPerfil" class="glass-card">
                <div class="mb-3">
                    <label class="small fw-bold text-muted">CORREO</label>
                    <input type="text" class="form-control bg-dark border-secondary text-white-50 small" value="<?= $correo ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold text-muted">NUEVA CLAVE</label>
                    <input type="password" name="password" class="form-control bg-dark border-secondary text-white small" placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-info w-100 fw-bold btn-sm py-2">ACTUALIZAR</button>
            </form>
        </div>
    </div>

    <script>
        // Splash Screen
        window.addEventListener("load", () => {
            setTimeout(() => {
                const splash = document.getElementById("splash-screen");
                splash.style.opacity = "0";
                setTimeout(() => splash.style.display = "none", 600);
            }, 800);
        });

        // Tabs
        function changeTab(tabId, element) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            document.getElementById('tab-' + tabId).classList.add('active');
            element.classList.add('active');
        }

        // Generar QR Identidad
        function generarQRIdentidad() {
            const contenedor = document.getElementById("qrcodeEstudiante");
            if(contenedor.innerHTML === "") {
                new QRCode(contenedor, {
                    text: "<?= $estudiante_id_format ?>",
                    width: 180, height: 180,
                    colorDark : "#000b1a", colorLight : "#ffffff"
                });
            }
        }

        // Registro de Asistencia
        function confirmarFinalizado() {
            const formData = new FormData();
            formData.append("clase", "Ciberseguridad y Redes");

            fetch("registrar_asistencia.php", { method: "POST", body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === "ok") {
                    const btn = document.getElementById("btnAsistenciaCheck");
                    btn.classList.remove("pulse-animation");
                    btn.style.background = "#198754";
                    btn.innerHTML = '<i class="bi bi-patch-check-fill"></i>';
                    btn.onclick = null;
                }
                alert(data.message || "Procesado");
            });
        }
    </script>
</body>
</html>
