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

$estudiante_id_format = "EST-" . str_pad($usuario_id, 5, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INATEC SOMOTO</title>
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

        body[data-theme="light"] {
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
            transition: background 0.4s ease, color 0.4s ease;
        }

        /* Arreglo desenfoque */
        .tab-content { display: none; transform: translateZ(0); -webkit-font-smoothing: antialiased; }
        .tab-content.active { display: block; animation: fadeInUp 0.4s ease-out; }

        .glass-card {
            background: var(--glass-card);
            backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 25px; padding: 22px;
            margin-bottom: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
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
            color: white; font-size: 1.8rem;
            position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%);
            z-index: 1001; border: 5px solid var(--glass-border);
            cursor: pointer; transition: 0.3s;
        }

        .bottom-nav {
            position: fixed; bottom: 0; width: 100%; height: 75px;
            background: var(--glass-card); backdrop-filter: blur(20px);
            display: flex; justify-content: space-around; align-items: center;
            z-index: 1000; border-top: 1px solid var(--glass-border);
        }

        .nav-item { color: var(--text-muted); text-align: center; cursor: pointer; flex: 1; transition: 0.3s; }
        .nav-item.active { color: var(--tech-cyan); }

        .badge-presente { background: rgba(0, 255, 128, 0.1); color: #00ff80; border: 1px solid rgba(0, 255, 128, 0.2); }
        .badge-ausente { background: rgba(255, 71, 87, 0.1); color: #ff4757; border: 1px solid rgba(255, 71, 87, 0.2); }
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
                <p class="small text-info mb-0">ESTUDIANTE</p>
            </div>
            <button class="btn border-0 text-white" id="theme-toggle" style="background: var(--glass-card); border-radius: 12px;">
                <i class="bi bi-moon-stars fs-5"></i>
            </button>
        </div>
    </div>

    <div class="main-container">
        <div id="tab-home" class="tab-content active">
            <div class="glass-card mt-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-muted">ASISTENCIA</span>
                    <span class="text-info"><?= $porcentaje ?>%</span>
                </div>
                <div style="height: 8px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden;">
                    <div style="width: <?= $porcentaje ?>%; height: 100%; background: linear-gradient(90deg, var(--primary-blue), var(--tech-cyan)); shadow: 0 0 10px var(--tech-cyan);"></div>
                </div>
            </div>

            <div class="glass-card border-start border-info border-4">
                <h6 class="fw-bold mb-1">Ciberseguridad y Redes</h6>
                <p class="small text-muted mb-0">Laboratorio A1 - Somoto</p>
            </div>

            <div class="glass-card text-center py-3" style="border: 1px dashed var(--tech-cyan);">
                <p class="small text-muted mb-1 text-uppercase fw-bold">Llave Dinámica</p>
                <h5 class="font-monospace text-info mb-0"><?= date('H') ?>•<?= substr(md5($usuario_id), 0, 4) ?>•<?= date('i') ?></h5>
            </div>
        </div>

        <div id="tab-reportes" class="tab-content">
            <h5 class="fw-bold mb-3">Historial</h5>
            <?php foreach ($historial as $h): ?>
                <div class="glass-card py-2 px-3 mb-2 d-flex justify-content-between align-items-center">
                    <span class="small fw-bold"><?= date('d/m/Y', strtotime($h['fecha'])) ?></span>
                    <span class="badge rounded-pill <?= $h['estado'] == 'Presente' ? 'badge-presente' : 'badge-ausente' ?>"><?= $h['estado'] ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="tab-carnet" class="tab-content text-center">
            <h5 class="fw-bold mb-3">ID Estudiantil</h5>
            <div class="glass-card">
                <div id="qrcodeEstudiante" class="d-flex justify-content-center mb-3"></div>
                <h6 class="fw-bold mb-0"><?= $nombre ?></h6>
                <p class="text-info small font-monospace"><?= $estudiante_id_format ?></p>
            </div>
        </div>
    </div>

    <div class="qr-fab pulse-animation" id="btnAsistenciaCheck" onclick="confirmarFinalizado()" style="background: linear-gradient(135deg, var(--primary-blue), var(--tech-cyan));">
        <i class="bi bi-qr-code-scan"></i>
    </div>

    <div class="bottom-nav">
        <div class="nav-item active" onclick="changeTab('home', this)">
            <i class="bi bi-cpu fs-4"></i><div class="small">Inicio</div>
        </div>
        <div class="nav-item" onclick="changeTab('reportes', this)">
            <i class="bi bi-journal-text fs-4"></i><div class="small">Bitácora</div>
        </div>
        <div style="flex: 0.3;"></div>
        <div class="nav-item" onclick="changeTab('carnet', this); generarQRIdentidad();">
            <i class="bi bi-qr-code-scan fs-4"></i><div class="small">ID-Tech</div>
        </div>
        <div class="nav-item" data-bs-toggle="offcanvas" data-bs-target="#panelPerfil">
            <i class="bi bi-person-gear fs-4"></i><div class="small">Perfil</div>
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
            <form id="formPerfil" class="glass-card mt-4 p-3 text-start">
                <label class="small fw-bold text-muted">CORREO</label>
                <input type="text" class="form-control bg-dark border-secondary text-white-50 mb-3 small" value="<?= $correo ?>" readonly>
                <button type="submit" class="btn btn-info w-100 btn-sm fw-bold">GUARDAR</button>
            </form>
            <a href="logout.php" class="btn btn-outline-danger btn-sm mt-3 w-100">Cerrar Sesión</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 1. MODO OSCURO / CLARO
        const themeBtn = document.getElementById('theme-toggle');
        themeBtn.addEventListener('click', () => {
            const body = document.body;
            const isDark = body.getAttribute('data-theme') === 'dark';
            const newTheme = isDark ? 'light' : 'dark';
            body.setAttribute('data-theme', newTheme);
            themeBtn.innerHTML = isDark ? '<i class="bi bi-sun fs-5"></i>' : '<i class="bi bi-moon-stars fs-5"></i>';
        });

        // 2. CAMBIO DE PESTAÑAS
        function changeTab(tabId, element) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
            document.getElementById('tab-' + tabId).classList.add('active');
            element.classList.add('active');
        }

        // 3. GENERAR QR
        function generarQRIdentidad() {
            const cont = document.getElementById("qrcodeEstudiante");
            if(cont.innerHTML === "") {
                new QRCode(cont, { text: "<?= $estudiante_id_format ?>", width: 150, height: 150 });
            }
        }

        // 4. SPLASH SCREEN
        window.onload = () => {
            setTimeout(() => document.getElementById('splash-screen').style.opacity = '0', 500);
            setTimeout(() => document.getElementById('splash-screen').style.display = 'none', 1100);
        };

        // 5. REGISTRO ASISTENCIA
        function confirmarFinalizado() {
            fetch("registrar_asistencia.php", { method: "POST" })
            .then(res => res.json())
            .then(data => {
                if(data.status === "ok") {
                    document.getElementById("btnAsistenciaCheck").style.background = "#198754";
                    document.getElementById("btnAsistenciaCheck").innerHTML = '<i class="bi bi-check-all"></i>';
                }
                alert(data.message);
            });
        }
    </script>
</body>
</html>
