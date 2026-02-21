<?php
require_once "config/db.php";
require_once "config/auth.php";
require_once "config/funciones.php"; // Cargamos el archivo global

/* Validar rol */
if (($_SESSION["rol"] ?? "") !== "estudiante") {
    header("Location: /index.php");
    exit();
}

$usuario_id = (int) $_SESSION["id"];
$nombre     = $_SESSION["nombre"];

/* 1. OBTENER DATOS ACTUALIZADOS */
$stmtUser = $db->prepare("SELECT correo FROM usuarios WHERE id = :id");
$stmtUser->execute([":id" => $usuario_id]);
$userData = $stmtUser->fetch();
$correo = $userData['correo'] ?? "estudiante@inatec.edu.ni";

/* 2. ESTADÍSTICAS Y ALERTAS (Usando funciones globales) */
$totalClases = (int) $db->query("SELECT COUNT(DISTINCT fecha) FROM asistencias")->fetchColumn();
$stmt = $db->prepare("SELECT COUNT(*) FROM asistencias WHERE usuario_id = :id");
$stmt->execute([":id" => $usuario_id]);
$asistidas = (int) $stmt->fetchColumn();
$porcentaje = $totalClases > 0 ? round(($asistidas / $totalClases) * 100) : 0;

// Verificar faltas consecutivas para la advertencia de riesgo
$mis_faltas_seguidas = obtenerFaltasConsecutivas($db, $usuario_id, 3);

/* 3. REGISTRO DE HOY */
$hoy = date('Y-m-d');
$stmtCheck = $db->prepare("SELECT COUNT(*) FROM asistencias WHERE usuario_id = :id AND fecha = :fecha");
$stmtCheck->execute([":id" => $usuario_id, ":fecha" => $hoy]);
$yaRegistroHoy = ($stmtCheck->fetchColumn() > 0);

/* 4. HISTORIAL DE ASISTENCIA */
$stmtHistorial = $db->prepare("
    SELECT f.fecha, 
           CASE WHEN a.id IS NOT NULL THEN 'Presente' ELSE 'Ausente' END AS estado
    FROM (SELECT DISTINCT fecha FROM asistencias) f
    LEFT JOIN asistencias a ON f.fecha = a.fecha AND a.usuario_id = :id
    ORDER BY f.fecha DESC LIMIT 8
");
$stmtHistorial->execute([":id" => $usuario_id]);
$historial = $stmtHistorial->fetchAll();

$estudiante_id_format = formatearID($usuario_id); // Usando función global
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGA - Estudiante Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="style-global.css">
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
            --glass-border: rgba(255, 255, 255, 0.4);
            --text-main: #1a2a3a;
            --text-muted: #5a6a7a;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-gradient) !important;
            background-attachment: fixed !important;
            min-height: 100vh;
            color: var(--text-main);
            margin: 0; padding-bottom: 100px;
            transition: all 0.4s ease;
        }

        h1, h2, h3, h4, h5, h6, span, p { color: var(--text-main); }

        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeInUp 0.4s ease-out; }

        .glass-card {
            background: var(--glass-card);
            backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 25px; padding: 22px;
            margin-bottom: 20px;
        }

        .header-section {
            padding: 40px 20px 80px;
            background: rgba(0, 0, 0, 0.05);
            border-bottom: 1px solid var(--glass-border);
            border-radius: 0 0 40px 40px;
        }

        .main-container { max-width: 500px; margin: -50px auto 0; padding: 0 15px; }

        .qr-fab {
            width: 70px; height: 70px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white !important; font-size: 1.8rem;
            position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%);
            z-index: 1001; border: 5px solid var(--glass-border);
            cursor: pointer; transition: 0.3s;
            background: linear-gradient(135deg, var(--primary-blue), var(--tech-cyan));
        }

        .bottom-nav {
            position: fixed; bottom: 0; width: 100%; height: 75px;
            background: var(--glass-card); backdrop-filter: blur(20px);
            display: flex; justify-content: space-around; align-items: center;
            z-index: 1000; border-top: 1px solid var(--glass-border);
        }

        .nav-item { color: var(--text-muted); text-align: center; cursor: pointer; flex: 1; }
        .nav-item.active i, .nav-item.active .small { color: var(--tech-cyan); }

        .badge-presente { background: rgba(0, 255, 128, 0.1); color: #00ff80 !important; border: 1px solid rgba(0, 255, 128, 0.2); }
        .badge-ausente { background: rgba(255, 71, 87, 0.1); color: #ff4757 !important; border: 1px solid rgba(255, 71, 87, 0.2); }
        
        .alert-desercion {
            background: rgba(255, 71, 87, 0.15);
            border: 1px solid #ff4757;
            border-radius: 20px; padding: 15px; margin-bottom: 20px;
        }
    </style>
</head>
<body data-theme="dark">

    <div id="splash-screen" style="position: fixed; inset: 0; background: var(--bg-gradient); z-index: 9999; display: flex; align-items: center; justify-content: center; transition: 0.6s;">
        <div class="spinner-border text-info"></div>
    </div>

    <div class="header-section text-center text-sm-start">
        <div class="d-flex justify-content-between align-items-center container" style="max-width: 500px;">
            <div>
                <h4 class="fw-bold mb-0">¡Hola, <?= explode(' ', trim($nombre))[0] ?>!</h4>
                <p class="small text-info mb-0 fw-bold">ESTUDIANTE</p>
            </div>
            <button class="btn border-0" id="theme-toggle" style="background: var(--glass-card); border-radius: 12px;">
                <i class="bi bi-moon-stars fs-5" id="theme-icon" style="color: white;"></i>
            </button>
        </div>
    </div>

    <div class="main-container">
        <?php if($mis_faltas_seguidas >= 3): ?>
            <div class="alert-desercion animate__animated animate__shakeX">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-octagon-fill text-danger fs-3 me-3"></i>
                    <div>
                        <h6 class="fw-bold text-danger mb-1">AVISO CRÍTICO</h6>
                        <p class="small mb-0">Riesgo de deserción por <b><?= $mis_faltas_seguidas ?> faltas</b>.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div id="tab-home" class="tab-content active">
            <div class="glass-card mt-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold opacity-75">ASISTENCIA</span>
                    <span class="text-info fw-bold"><?= $porcentaje ?>%</span>
                </div>
                <div style="height: 8px; background: rgba(0,0,0,0.2); border-radius: 10px; overflow: hidden;">
                    <div style="width: <?= $porcentaje ?>%; height: 100%; background: linear-gradient(90deg, var(--primary-blue), var(--tech-cyan));"></div>
                </div>
            </div>

            <div class="glass-card border-start border-info border-4">
                <h6 class="fw-bold mb-1">Ciberseguridad y Redes</h6>
                <p class="small opacity-75 mb-0">Laboratorio A1 - Somoto</p>
            </div>

            <div class="glass-card text-center py-3" style="border: 1px dashed var(--tech-cyan);">
                <p class="small opacity-75 mb-1 text-uppercase fw-bold">Llave Dinámica</p>
                <h5 class="font-monospace text-info mb-0 fw-bold"><?= date('H') ?>•<?= substr(md5($usuario_id), 0, 4) ?>•<?= date('i') ?></h5>
            </div>
        </div>

        <div id="tab-reportes" class="tab-content">
            <h5 class="fw-bold mb-3">Historial Reciente</h5>
            <?php foreach ($historial as $h): ?>
                <div class="glass-card py-2 px-3 mb-2 d-flex justify-content-between align-items-center">
                    <span class="small fw-bold"><?= date('d/m/Y', strtotime($h['fecha'])) ?></span>
                    <span class="badge rounded-pill <?= $h['estado'] == 'Presente' ? 'badge-presente' : 'badge-ausente' ?>"><?= $h['estado'] ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="tab-carnet" class="tab-content text-center">
            <h5 class="fw-bold mb-3">ID Digital</h5>
            <div class="glass-card">
                <div id="qrcodeEstudiante" class="d-flex justify-content-center mb-3"></div>
                <h6 class="fw-bold mb-0"><?= $nombre ?></h6>
                <p class="text-info small font-monospace fw-bold"><?= $estudiante_id_format ?></p>
            </div>
        </div>
    </div>

    <div class="qr-fab" id="btnAsistenciaCheck" onclick="confirmarAsistencia()">
        <i class="bi bi-qr-code-scan"></i>
    </div>

    <div class="bottom-nav">
        <div class="nav-item active" onclick="changeTab('home', this)">
            <i class="bi bi-cpu fs-4"></i><div class="small" style="font-size: 0.6rem;">Inicio</div>
        </div>
        <div class="nav-item" onclick="changeTab('reportes', this)">
            <i class="bi bi-journal-text fs-4"></i><div class="small" style="font-size: 0.6rem;">Bitácora</div>
        </div>
        <div style="flex: 0.3;"></div>
        <div class="nav-item" onclick="changeTab('carnet', this); generarQRIdentidad();">
            <i class="bi bi-qr-code-scan fs-4"></i><div class="small" style="font-size: 0.6rem;">ID-Tech</div>
        </div>
        <div class="nav-item" data-bs-toggle="offcanvas" data-bs-target="#panelPerfil">
            <i class="bi bi-person-gear fs-4"></i><div class="small" style="font-size: 0.6rem;">Perfil</div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="panelPerfil" style="background: var(--bg-gradient); color: var(--text-main); width: 280px;">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="fw-bold mb-0">Mi Perfil</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body text-center">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=00d4ff&color=000" class="rounded-circle mb-3" width="80">
            <h6 class="fw-bold"><?= $nombre ?></h6>
            <div class="glass-card mt-4 p-3 text-start">
                <label class="small fw-bold opacity-50">CORREO</label>
                <input type="text" class="form-control form-control-sm bg-transparent border-secondary text-white-50 mb-3" value="<?= $correo ?>" readonly>
                <button class="btn btn-info w-100 btn-sm fw-bold">ACTUALIZAR</button>
            </div>
            <a href="logout.php" class="btn btn-outline-danger btn-sm mt-3 w-100 rounded-pill">Cerrar Sesión</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cambio de Temas
        const themeBtn = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        themeBtn.addEventListener('click', () => {
            const body = document.body;
            const isDark = body.getAttribute('data-theme') === 'dark';
            const newTheme = isDark ? 'light' : 'dark';
            body.setAttribute('data-theme', newTheme);
            themeIcon.className = isDark ? 'bi bi-sun fs-5' : 'bi bi-moon-stars fs-5';
            themeIcon.style.color = isDark ? '#1a2a3a' : '#ffffff';
        });

        // Navegación de Pestañas
        function changeTab(tabId, element) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
            document.getElementById('tab-' + tabId).classList.add('active');
            element.classList.add('active');
        }

        // QR de Identidad Estudiantil
        function generarQRIdentidad() {
            const cont = document.getElementById("qrcodeEstudiante");
            if(cont.innerHTML === "") {
                new QRCode(cont, { text: "<?= $estudiante_id_format ?>", width: 160, height: 160 });
            }
        }

        // Pantalla de carga
        window.onload = () => {
            setTimeout(() => {
                const splash = document.getElementById('splash-screen');
                splash.style.opacity = '0';
                setTimeout(() => splash.style.display = 'none', 600);
            }, 500);
        };

        // Función de registro
        function confirmarAsistencia() {
            fetch("registrar_asistencia.php", { method: "POST" })
            .then(res => res.json())
            .then(data => {
                if(data.status === "ok") {
                    const fab = document.getElementById("btnAsistenciaCheck");
                    fab.style.background = "#198754";
                    fab.innerHTML = '<i class="bi bi-check-all"></i>';
                }
                alert(data.message);
            });
        }
    </script>
</body>
</html>
