<?php
// 1. INICIO DE SESIÓN Y SEGURIDAD
require_once "config/auth.php"; 
require_once "config/db.php";
require_once "config/funciones.php"; 
require_once "config/security.php"; 

if (($_SESSION["rol"] ?? "") !== "estudiante") {
    header("Location: /index.php");
    exit();
}

$usuario_id = (int) $_SESSION["id"];
$nombre     = $_SESSION["nombre"];

/* OBTENER DATOS */
$stmtUser = $db->prepare("SELECT correo FROM usuarios WHERE id = :id");
$stmtUser->execute([":id" => $usuario_id]);
$userData = $stmtUser->fetch();
$correo = $userData['correo'] ?? "estudiante@inatec.edu.ni";

/* ESTADÍSTICAS */
$totalClases = (int) $db->query("SELECT COUNT(DISTINCT fecha) FROM asistencias")->fetchColumn();
$stmt = $db->prepare("SELECT COUNT(*) FROM asistencias WHERE usuario_id = :id");
$stmt->execute([":id" => $usuario_id]);
$asistidas = (int) $stmt->fetchColumn();
$porcentaje = $totalClases > 0 ? round(($asistidas / $totalClases) * 100) : 0;

$mis_faltas_seguidas = obtenerFaltasConsecutivas($db, $usuario_id, 3);
$estudiante_id_format = formatearID($usuario_id);
$estaEnRed = esRedInatec(); 

/* HISTORIAL */
$stmtHistorial = $db->prepare("
    SELECT f.fecha, 
           CASE WHEN a.id IS NOT NULL THEN 'Presente' ELSE 'Ausente' END AS estado
    FROM (SELECT DISTINCT fecha FROM asistencias) f
    LEFT JOIN asistencias a ON f.fecha = a.fecha AND a.usuario_id = :id
    ORDER BY f.fecha DESC LIMIT 6
");
$stmtHistorial->execute([":id" => $usuario_id]);
$historial = $stmtHistorial->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGA - Dashboard Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            /* MODO OSCURO (Default) */
            --bg-body: radial-gradient(circle at top right, #001f3f, #00050a);
            --text-main: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.7);
            --glass: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.15);
            --primary: #00d4ff;
            --nav-bg: rgba(0, 0, 0, 0.4);
        }

        [data-theme="light"] {
            /* MODO CLARO */
            --bg-body: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --text-main: #1a2a3a;
            --text-muted: #5a6a7a;
            --glass: rgba(255, 255, 255, 0.8);
            --glass-border: rgba(0, 0, 0, 0.1);
            --primary: #007bff;
            --nav-bg: rgba(255, 255, 255, 0.8);
        }

        body { 
            background: var(--bg-body) !important; 
            color: var(--text-main); 
            font-family: 'Inter', sans-serif; 
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .text-muted { color: var(--text-muted) !important; }
        h1, h2, h3, h4, h5, h6 { color: var(--text-main); }

        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        /* Selector de Tema */
        .theme-toggle {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
        }

        /* FAB Asistencia */
        .qr-fab {
            position: fixed; bottom: 90px; left: 50%; transform: translateX(-50%);
            width: 65px; height: 65px; border-radius: 50%;
            background: linear-gradient(135deg, #00d4ff, #004a99);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 25px rgba(0, 212, 255, 0.4);
            cursor: pointer; z-index: 1000; transition: 0.3s;
            color: white !important;
        }
        .qr-fab.disabled { filter: grayscale(1); opacity: 0.5; cursor: not-allowed; }

        /* Nav Bottom */
        .bottom-nav {
            position: fixed; bottom: 0; width: 100%; height: 75px;
            background: var(--nav-bg); backdrop-filter: blur(20px);
            display: flex; justify-content: space-around; align-items: center;
            border-top: 1px solid var(--glass-border);
            z-index: 999;
        }
        .nav-item { color: var(--text-muted); font-size: 1.2rem; cursor: pointer; text-align: center; text-decoration: none; }
        .nav-item.active { color: var(--primary); }
        .nav-label { font-size: 0.65rem; display: block; margin-top: 2px; }

        /* Progress Bar */
        .progress-container { height: 8px; background: rgba(127,127,127,0.2); border-radius: 10px; overflow: hidden; }
    </style>
</head>
<body data-theme="dark">

<div class="container pt-4 pb-5" style="max-width: 450px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h5 class="mb-0 fw-bold">Hola, <?= explode(' ', $nombre)[0] ?></h5>
            <span class="badge bg-info text-dark" style="font-size: 0.65rem; font-weight: 600;">ESTUDIANTE</span>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="theme-toggle" id="themeBtn" onclick="toggleTheme()">
                <i class="bi bi-moon-stars" id="themeIcon"></i>
            </div>
            <img src="https://ui-avatars.com/api/?name=<?= $nombre ?>&background=00d4ff&color=000" class="rounded-circle border border-2 border-info" width="42">
        </div>
    </div>

    <?php if(!$estaEnRed): ?>
        <div class="alert alert-warning py-2 rounded-4 border-0 mb-4 d-flex align-items-center" style="background: rgba(255, 193, 7, 0.15); color: #ffca2c;">
            <i class="bi bi-wifi-off fs-5 me-2"></i> 
            <small class="fw-medium">Conéctate al WiFi del centro para marcar asistencia.</small>
        </div>
    <?php endif; ?>

    <div id="home" class="tab-content active">
        <div class="glass-card text-center">
            <span class="text-muted small fw-bold text-uppercase tracking-wider">Mi Asistencia Total</span>
            <h2 class="fw-bold my-2" style="font-size: 2.5rem;"><?= $porcentaje ?>%</h2>
            <div class="progress-container">
                <div style="width: <?= $porcentaje ?>%; height: 100%; background: var(--primary);"></div>
            </div>
        </div>

        <div class="glass-card">
            <h6 class="fw-bold mb-1">Ciberseguridad y Redes</h6>
            <p class="small text-muted mb-0"><i class="bi bi-geo-alt me-1"></i> Laboratorio A1 - Somoto</p>
        </div>

        <div class="glass-card text-center border-info border-opacity-25" style="border-style: dashed; background: rgba(0, 212, 255, 0.03);">
            <p class="small text-info mb-1 fw-bold">LLAVE DINÁMICA</p>
            <h4 class="font-monospace mb-0 fw-bold" style="letter-spacing: 2px;">
                <?= date('H') ?>•<?= substr(md5($usuario_id), 0, 4) ?>•<?= date('i') ?>
            </h4>
        </div>
    </div>

    <div id="historial" class="tab-content">
        <h6 class="mb-3 ps-2 fw-bold">Historial de Clases</h6>
        <?php foreach($historial as $h): ?>
            <div class="glass-card py-2 d-flex justify-content-between align-items-center">
                <span class="fw-medium small"><?= date('d/m/Y', strtotime($h['fecha'])) ?></span>
                <span class="badge rounded-pill <?= $h['estado']=='Presente' ? 'bg-success':'bg-danger' ?> bg-opacity-25 text-<?= $h['estado']=='Presente' ? 'success':'danger' ?>" style="font-size: 0.7rem;">
                    <?= $h['estado'] ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="id-digital" class="tab-content text-center">
        <div class="glass-card py-4">
            <div id="qrcode" class="d-flex justify-content-center mb-3 p-3 bg-white rounded-4 shadow-sm mx-auto" style="width: fit-content;"></div>
            <h5 class="fw-bold mb-1"><?= $nombre ?></h5>
            <p class="text-info small font-monospace mb-0"><?= $estudiante_id_format ?></p>
            <p class="text-muted small mt-2"><?= $correo ?></p>
        </div>
    </div>

</div>

<div class="qr-fab <?= !$estaEnRed ? 'disabled' : '' ?>" id="btnAsistencia" onclick="marcarAsistencia()">
    <i class="bi bi-qr-code-scan fs-2"></i>
</div>

<nav class="bottom-nav">
    <div class="nav-item active" onclick="showTab('home', this)">
        <i class="bi bi-grid-1x2-fill"></i><span class="nav-label">Inicio</span>
    </div>
    <div class="nav-item" onclick="showTab('historial', this)">
        <i class="bi bi-calendar-check"></i><span class="nav-label">Bitácora</span>
    </div>
    <div style="width: 60px;"></div>
    <div class="nav-item" onclick="showTab('id-digital', this); generateQR();">
        <i class="bi bi-person-badge"></i><span class="nav-label">ID Tech</span>
    </div>
    <div class="nav-item" onclick="window.location.href='logout.php'">
        <i class="bi bi-box-arrow-right"></i><span class="nav-label">Salir</span>
    </div>
</nav>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    // 1. Cambio de Pestañas (Corregido para asegurar que los IDs existan)
    function showTab(id, el) {
        const targetTab = document.getElementById(id);
        if (!targetTab) return; // Seguridad: si la pestaña no existe, no hace nada

        // Ocultar todas las pestañas
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        // Quitar estado activo a todos los iconos de navegación
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
        
        // Mostrar la seleccionada
        targetTab.classList.add('active');
        if (el) el.classList.add('active');
    }

    // 2. Cambio de Tema (Con persistencia opcional)
    function toggleTheme() {
        const body = document.body;
        const icon = document.getElementById('themeIcon');
        if (!icon) return;

        const currentTheme = body.getAttribute('data-theme');
        if (currentTheme === 'dark') {
            body.setAttribute('data-theme', 'light');
            icon.className = 'bi bi-sun';
            icon.style.color = '#1a2a3a';
        } else {
            body.setAttribute('data-theme', 'dark');
            icon.className = 'bi bi-moon-stars';
            icon.style.color = '#ffffff';
        }
    }

    // 3. Generar QR para el Carnet (Corregido ID)
    function generateQR() {
        const qrBox = document.getElementById("qrcode");
        if (!qrBox) return;

        if (qrBox.innerHTML.trim() === "") {
            new QRCode(qrBox, { 
                text: "<?= $estudiante_id_format ?>", 
                width: 160, 
                height: 160, 
                colorDark : "#000000", 
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        }
    }

    // 4. Lógica de Asistencia (Sincronizada con el botón flotante)
    function marcarAsistencia() {
        // Verificación de red desde PHP
        const estaEnRed = <?= $estaEnRed ? 'true' : 'false' ?>;
        
        if (!estaEnRed) {
            alert("⚠️ Acceso denegado: Debes estar conectado al WiFi oficial del centro.");
            return;
        }

        const btn = document.getElementById('btnAsistencia');
        if (!btn) return;

        const iconOriginal = '<i class="bi bi-qr-code-scan fs-2"></i>';
        
        // Estado de carga
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        btn.style.pointerEvents = 'none'; 

        fetch("registrar_asistencia.php", { 
            method: "POST" 
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === "ok") {
                alert("✅ " + (data.message || "Asistencia registrada con éxito"));
                location.reload(); 
            } 
            else if (data.status === "existe") {
                alert("ℹ️ " + (data.message || "Ya habías registrado tu asistencia hoy."));
                btn.innerHTML = iconOriginal;
                btn.style.pointerEvents = 'auto';
            } 
            else {
                alert("❌ Error: " + data.message);
                btn.innerHTML = iconOriginal;
                btn.style.pointerEvents = 'auto';
            }
        })
        .catch(err => {
            console.error("Error:", err);
            alert("🚨 Error de comunicación con el servidor.");
            btn.innerHTML = iconOriginal;
            btn.style.pointerEvents = 'auto';
        });
    }

    // Auto-ejecución al cargar (Opcional: para limpiar splash screen si lo tienes)
    window.onload = () => {
        console.log("Sistema SGA Cargado correctamente.");
    };
</script>



</body>
</html>
