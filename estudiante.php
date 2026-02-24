<?php
// 1. INICIO DE SESIÓN Y SEGURIDAD (Sin espacios antes del <?php)
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
            --primary: #00d4ff;
            --accent: #004a99;
            --glass: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.12);
        }
        body { 
            background: radial-gradient(circle at top right, #001f3f, #00050a); 
            color: #fff; font-family: 'Inter', sans-serif; min-height: 100vh;
        }
        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        /* FAB Asistencia */
        .qr-fab {
            position: fixed; bottom: 90px; left: 50%; transform: translateX(-50%);
            width: 65px; height: 65px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 25px rgba(0, 212, 255, 0.4);
            cursor: pointer; z-index: 1000; transition: 0.3s;
        }
        .qr-fab.disabled { filter: grayscale(1); opacity: 0.5; cursor: not-allowed; }

        /* Nav Bottom */
        .bottom-nav {
            position: fixed; bottom: 0; width: 100%; height: 75px;
            background: rgba(0, 0, 0, 0.3); backdrop-filter: blur(20px);
            display: flex; justify-content: space-around; align-items: center;
            border-top: 1px solid var(--glass-border);
        }
        .nav-item { color: rgba(255,255,255,0.5); font-size: 1.2rem; cursor: pointer; text-align: center; }
        .nav-item.active { color: var(--primary); }
        .nav-label { font-size: 0.65rem; display: block; }
    </style>
</head>
<body>

<div class="container pt-4 pb-5" style="max-width: 450px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h5 class="mb-0 fw-bold">Hola, <?= explode(' ', $nombre)[0] ?></h5>
            <span class="badge bg-info text-dark" style="font-size: 0.6rem;">ID: <?= $estudiante_id_format ?></span>
        </div>
        <img src="https://ui-avatars.com/api/?name=<?= $nombre ?>&background=00d4ff&color=000" class="rounded-circle border border-2 border-info" width="45">
    </div>

    <?php if(!$estaEnRed): ?>
        <div class="alert alert-warning py-2 rounded-4 border-0 mb-4" style="background: rgba(255, 193, 7, 0.15); color: #ffca2c;">
            <i class="bi bi-wifi-off me-2"></i> <small>Conéctate al WiFi del centro para marcar.</small>
        </div>
    <?php endif; ?>

    <div id="home" class="tab-content active">
        <div class="glass-card text-center">
            <span class="text-muted small">MI ASISTENCIA TOTAL</span>
            <h2 class="fw-bold my-1"><?= $porcentaje ?>%</h2>
            <div class="progress mt-2" style="height: 6px; background: rgba(255,255,255,0.1);">
                <div class="progress-bar bg-info" style="width: <?= $porcentaje ?>%"></div>
            </div>
        </div>

        <div class="glass-card">
            <h6 class="fw-bold mb-1">Ciberseguridad y Redes</h6>
            <p class="small text-muted mb-0"><i class="bi bi-geo-alt me-1"></i> Laboratorio A1 - Somoto</p>
        </div>

        <div class="glass-card text-center border-info border-opacity-25" style="border-style: dashed;">
            <p class="small text-info mb-1 fw-bold">LLAVE DINÁMICA</p>
            <h4 class="font-monospace mb-0"><?= date('H') ?>•<?= substr(md5($usuario_id), 0, 4) ?>•<?= date('i') ?></h4>
        </div>
    </div>

    <div id="historial" class="tab-content">
        <h6 class="mb-3 ps-2">Historial Reciente</h6>
        <?php foreach($historial as $h): ?>
            <div class="glass-card py-2 d-flex justify-content-between align-items-center">
                <span class="small"><?= date('d/m/Y', strtotime($h['fecha'])) ?></span>
                <span class="badge <?= $h['estado']=='Presente' ? 'bg-success':'bg-danger' ?> bg-opacity-25 text-<?= $h['estado']=='Presente' ? 'success':'danger' ?>">
                    <?= $h['estado'] ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="id-digital" class="tab-content text-center">
        <div class="glass-card py-4">
            <div id="qrcode" class="d-flex justify-content-center mb-3"></div>
            <h5 class="fw-bold mb-0"><?= $nombre ?></h5>
            <p class="text-muted small"><?= $correo ?></p>
        </div>
    </div>

</div>

<div class="qr-fab <?= !$estaEnRed ? 'disabled' : '' ?>" id="btnAsistencia" onclick="marcarAsistencia()">
    <i class="bi bi-qr-code-scan fs-2 text-white"></i>
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
        <i class="bi bi-person-badge"></i><span class="nav-label">Carnet</span>
    </div>
    <div class="nav-item" onclick="window.location.href='logout.php'">
        <i class="bi bi-box-arrow-right"></i><span class="nav-label">Salir</span>
    </div>
</nav>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    function showTab(id, el) {
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        el.classList.add('active');
    }

    function generateQR() {
        const qrBox = document.getElementById("qrcode");
        if(qrBox.innerHTML === "") {
            new QRCode(qrBox, { text: "<?= $estudiante_id_format ?>", width: 180, height: 180, colorDark : "#000000", colorLight : "#ffffff" });
        }
    }

    function marcarAsistencia() {
        if(<?= $estaEnRed ? 'false' : 'true' ?>) {
            alert("No estás conectado a la red autorizada.");
            return;
        }
        const btn = document.getElementById('btnAsistencia');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        fetch("registrar_asistencia.php", { method: "POST" })
        .then(r => r.json())
        .then(data => {
            alert(data.message);
            location.reload();
        }).catch(() => {
            alert("Error de conexión");
            btn.innerHTML = '<i class="bi bi-qr-code-scan fs-2 text-white"></i>';
        });
    }
</script>

</body>
</html>
