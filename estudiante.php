<?php
// 1. CONFIGURACIÓN DE ENTORNO Y SEGURIDAD
date_default_timezone_set('America/Managua');
require_once "config/auth.php";
require_once "config/db.php"; 

if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "maestro") {
    header("Location: /index.php");
    exit();
}

$nombre = $_SESSION["nombre"];
$hoy = date('Y-m-d');

// --- NUEVA LÓGICA: CONSULTA SI YA SE ACTIVÓ ASISTENCIA HOY ---
// (Asumimos que si hay al menos un registro de asistencia hoy, la sesión ya fue abierta)
$sql_check = "SELECT COUNT(*) FROM asistencias WHERE DATE(fecha) = :hoy";
$stmt_check = $db->prepare($sql_check);
$stmt_check->execute([':hoy' => $hoy]);
$asistencia_realizada = ($stmt_check->fetchColumn() > 0);

// 2. OBTENCIÓN DE DATOS ESTUDIANTES
try {
    $sql = "SELECT u.id, u.nombre, u.usuario, 
               CASE WHEN a.id IS NOT NULL THEN 'Presente' ELSE 'Ausente' END AS estado_hoy
           FROM usuarios u
           LEFT JOIN asistencias a ON u.id = a.usuario_id AND DATE(a.fecha) = :hoy
           WHERE u.rol_id = 3 
           ORDER BY u.nombre ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute([':hoy' => $hoy]);
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_alumnos = count($estudiantes);
    $presentes = 0;
    foreach($estudiantes as $e) { if($e['estado_hoy'] == 'Presente') $presentes++; }
    $porcentaje_asistencia = ($total_alumnos > 0) ? round(($presentes / $total_alumnos) * 100) : 0;
} catch (Exception $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?> 

<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGA - Maestro Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #00d4ff;
            --success: #00ffa3;
            --bg-body: radial-gradient(circle at top right, #001f3f, #00050a);
            --glass: rgba(255, 255, 255, 0.07);
            --text-main: #ffffff;
        }

        [data-theme="light"] {
            --bg-body: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --text-main: #1a2a3a;
        }

        body {
            background: var(--bg-body) !important;
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* --- BOTÓN ANIMADO --- */
        .btn-status {
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            padding: 10px 25px;
            font-weight: 700;
            border: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Estado Inicial (Azul) */
        .btn-active-qr {
            background: linear-gradient(135deg, var(--primary), #004a99);
            color: white;
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.3);
        }

        /* Estado Completado (Verde) */
        .btn-completed {
            background: rgba(0, 255, 163, 0.1) !important;
            color: var(--success) !important;
            border: 1px solid var(--success) !important;
            cursor: default;
            pointer-events: none; /* Desactiva clics */
            animation: pulseSuccess 2s infinite;
        }

        @keyframes pulseSuccess {
            0% { box-shadow: 0 0 0 0 rgba(0, 255, 163, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(0, 255, 163, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 255, 163, 0); }
        }

        .search-container { position: relative; min-width: 280px; }
        #studentSearch {
            background: var(--glass) !important;
            color: var(--text-main) !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            padding-left: 2.8rem !important;
            border-radius: 12px;
        }
        .search-icon { position: absolute; top: 50%; left: 15px; transform: translateY(-50%); color: var(--primary); }
        
        .table-custom { width: 100%; border-spacing: 0 8px; border-collapse: separate; }
        .table-custom tr { background: var(--glass); border-radius: 15px; }
        .table-custom td { padding: 1rem; vertical-align: middle; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block p-4" style="background: rgba(0,0,0,0.2); min-height: 100vh;">
            <div class="text-center mb-5">
                <h6 class="fw-bold mb-0"><?= $nombre ?></h6>
                <small class="text-info">MAESTRO</small>
            </div>
            <div class="nav flex-column gap-2">
                <a href="maestro.php" class="nav-link text-white active"><i class="bi bi-grid-fill me-2"></i> Dashboard</a>
                <a href="logout.php" class="nav-link text-danger mt-5"><i class="bi bi-door-open me-2"></i> Salir</a>
            </div>
        </nav>

        <main class="col-md-10 p-4">
            <header class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-0">Gestión de <span class="text-info">Asistencia</span></h3>
                    <p class="text-muted small mb-0"><?= date('l, d F Y') ?></p>
                </div>
                
                <div class="d-flex gap-3 align-items-center">
                    <div class="theme-toggle" onclick="toggleTheme()" style="cursor:pointer;">
                        <i class="bi bi-moon-stars" id="themeIcon"></i>
                    </div>

                    <?php if ($asistencia_realizada): ?>
                        <div class="btn-status btn-completed">
                            <i class="bi bi-check-circle-fill"></i> ASISTENCIA REGISTRADA
                        </div>
                    <?php else: ?>
                        <button class="btn-status btn-active-qr" onclick="generarQR()">
                            <i class="bi bi-qr-code-scan"></i> ACTIVAR QR
                        </button>
                    <?php if ($asistencia_realizada): ?>
    
<?php endif; ?>
                    <?php endif; ?>
                </div>
            </header>

            <div class="row g-3 mb-4 text-center">
                <div class="col-md-4">
                    <div class="glass-card border-bottom border-primary border-4">
                        <small class="fw-bold text-uppercase" style="color:var(--text-main)">Promedio Hoy</small>
                        <h2 class="fw-bold mb-0 text-primary"><?= $porcentaje_asistencia ?>%</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card border-bottom border-success border-4">
                        <small class="fw-bold text-uppercase" style="color:var(--text-main)">Presentes</small>
                        <h2 class="fw-bold mb-0 text-success"><?= $presentes ?> / <?= $total_alumnos ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card border-bottom border-danger border-4">
                        <small class="fw-bold text-uppercase" style="color:var(--text-main)">Ausentes</small>
                        <h2 class="fw-bold mb-0 text-danger"><?= $total_alumnos - $presentes ?></h2>
                    </div>
                </div>
            </div>

            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Listado de Estudiantes</h5>
                    <div class="search-container">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" id="studentSearch" class="form-control" placeholder="Filtrar alumno...">
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table-custom" id="attendanceTable">
                        <thead>
                            <tr class="text-info small fw-bold">
                                <td>ESTUDIANTE</td>
                                <td class="text-center">USUARIO</td>
                                <td class="text-center">ESTADO</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estudiantes as $est): ?>
                            <tr class="student-row">
                                <td><span class="fw-semibold student-name"><?= $est['nombre'] ?></span></td>
                                <td class="text-center text-info">@<?= $est['usuario'] ?></td>
                                <td class="text-center">
                                    <span class="badge rounded-pill <?= ($est['estado_hoy'] === 'Presente') ? 'bg-success' : 'bg-danger'; ?> px-3">
                                        <?= strtoupper($est['estado_hoy']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="modalAsistencia" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 bg-dark text-white rounded-5">
            <div class="modal-body text-center p-5">
                <i class="bi bi-broadcast fs-1 text-info mb-3"></i>
                <h4 class="fw-bold">Código QR de Asistencia</h4>
                <div id="contenedorQR" class="mx-auto my-4 p-3 bg-white rounded-4" style="width: fit-content;"></div>
                <button class="btn btn-outline-danger w-100 rounded-pill py-3 fw-bold" data-bs-dismiss="modal">CERRAR</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    // Buscador
    document.getElementById('studentSearch').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll('.student-row').forEach(row => {
            let name = row.querySelector('.student-name').innerText.toLowerCase();
            row.style.display = name.includes(val) ? '' : 'none';
        });
    });

    // QR
    function generarQR() {
        const contenedor = document.getElementById("contenedorQR");
        contenedor.innerHTML = ""; 
        const url = window.location.origin + "/procesar_qr.php?clase=ClaseActual&fecha=<?= $hoy ?>";
        new QRCode(contenedor, { text: url, width: 220, height: 220 });
        new bootstrap.Modal(document.getElementById('modalAsistencia')).show();
    }

    // Tema
    function toggleTheme() {
        const body = document.documentElement;
        let theme = body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        body.setAttribute('data-theme', theme);
        localStorage.setItem('sga_theme', theme);
    }
</script>
</body>
</html>
