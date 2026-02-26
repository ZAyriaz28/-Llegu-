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

// 2. OBTENCIÓN DE DATOS (SQL Corregido para DATETIME)
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
            /* MODO OSCURO (Optimizado para alto contraste) */
            --primary: #00d4ff; /* Cian vibrante */
            --secondary: #004a99;
            --success: #00ffa3; /* Verde neón */
            --danger: #ff4757;
            --bg-body: radial-gradient(circle at top right, #001f3f, #00050a);
            --glass: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.15);
            --text-main: #ffffff;
            --text-muted: #b0c4de; /* Azul grisáceo claro (más legible que el anterior) */
            --input-bg: rgba(0, 0, 0, 0.3);
        }

        [data-theme="light"] {
            --bg-body: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --glass: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(0, 0, 0, 0.1);
            --text-main: #1a2a3a;
            --text-muted: #5a6a7a;
            --primary: #007bff;
            --input-bg: #ffffff;
        }

        body {
            background: var(--bg-body) !important;
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            transition: all 0.4s ease;
        }

        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Estilo mejorado para el promedio y números */
        .stat-label {
            color: var(--text-muted) !important;
            letter-spacing: 1px;
            font-size: 0.75rem;
        }

        /* Input de búsqueda con mejor contraste */
        .search-container {
            position: relative;
            width: 300px;
        }
        .search-container input {
            background: var(--input-bg) !important;
            border: 1px solid var(--glass-border) !important;
            color: white !important;
            padding-left: 2.8rem;
            border-radius: 12px;
        }
        .search-container input::placeholder {
            color: var(--text-muted);
            opacity: 0.8;
        }
        .search-container i {
            color: var(--primary);
        }

        /* Tabla y textos de usuario */
        .table-custom { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        .table-custom tr { background: var(--glass); transition: 0.2s; }
        .table-custom td { padding: 1.1rem; border: none; vertical-align: middle; }
        .table-custom td:first-child { border-radius: 15px 0 0 15px; }
        .table-custom td:last-child { border-radius: 0 15px 15px 0; }

        .student-user {
            color: var(--primary) !important; /* Ahora el @usuario resalta más */
            font-weight: 500;
            opacity: 0.9;
        }

        .badge-presente { background: rgba(0, 255, 163, 0.2) !important; color: #00ffa3 !important; border: 1px solid #00ffa3; }
        .badge-ausente { background: rgba(255, 71, 87, 0.2) !important; color: #ff4757 !important; border: 1px solid #ff4757; }

        .btn-neon {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; border: none; font-weight: 700;
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.3); border-radius: 12px;
        }

        .sidebar-tech { background: rgba(0,0,0,0.2); border-right: 1px solid var(--glass-border); min-height: 100vh; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block sidebar-tech p-4">
            <div class="text-center mb-5">
                <div class="bg-primary mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold" 
                     style="width: 50px; height: 50px; border-radius: 50%; color: #000; box-shadow: 0 0 20px rgba(0,212,255,0.4);">
                    <?= substr($nombre, 0, 1) ?>
                </div>
                <h6 class="fw-bold mb-0 text-white"><?= $nombre ?></h6>
                <small class="text-info">MAESTRO ADMIN</small>
            </div>
            <div class="nav flex-column gap-2">
                <a href="#" class="nav-link text-white active"><i class="bi bi-cpu me-2"></i> Dashboard</a>
                <a href="historial_asistencias.php" class="nav-link text-white-50"><i class="bi bi-folder2-open me-2"></i> Historial</a>
                <a href="logout.php" class="nav-link text-danger mt-5"><i class="bi bi-power me-2"></i> Salir</a>
            </div>
        </nav>

        <main class="col-md-10 p-4">
            <header class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h3 class="fw-bold mb-0 text-white">Panel de <span class="text-info">Control</span></h3>
                    <p class="text-muted small mb-0">Somoto • <?= date('d M, Y') ?></p>
                </div>
                <div class="d-flex gap-3">
                    <div class="theme-toggle btn" onclick="toggleTheme()" style="background: var(--glass); border: 1px solid var(--glass-border); color: white;">
                        <i class="bi bi-moon-stars" id="themeIcon"></i>
                    </div>
                    <button class="btn btn-neon px-4" onclick="generarQR()">
                        <i class="bi bi-qr-code-scan me-2"></i> ACTIVAR QR
                    </button>
                </div>
            </header>

            <div class="row g-3 mb-5 text-center">
                <div class="col-md-4">
                    <div class="glass-card border-bottom border-primary border-4">
                        <small class="stat-label fw-bold d-block mb-1">PROMEDIO DE ASISTENCIA</small>
                        <h2 class="fw-bold mb-0 text-primary" style="text-shadow: 0 0 10px rgba(0,212,255,0.3);"><?= $porcentaje_asistencia ?>%</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card border-bottom border-success border-4">
                        <small class="stat-label fw-bold d-block mb-1">ALUMNOS PRESENTES</small>
                        <h2 class="fw-bold mb-0 text-success" style="text-shadow: 0 0 10px rgba(0,255,163,0.3);"><?= $presentes ?> / <?= $total_alumnos ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card border-bottom border-danger border-4">
                        <small class="stat-label fw-bold d-block mb-1">TOTAL AUSENTES</small>
                        <h2 class="fw-bold mb-0 text-danger"><?= $total_alumnos - $presentes ?></h2>
                    </div>
                </div>
            </div>

            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0 text-white">Lista de Estudiantes</h5>
                    <div class="search-container">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                        <input type="text" id="studentSearch" class="form-control" placeholder="Buscar por nombre...">
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table-custom" id="attendanceTable">
                        <thead>
                            <tr class="text-info small fw-bold">
                                <td>ESTUDIANTE</td>
                                <td class="text-center">USUARIO</td>
                                <td class="text-center">ESTADO</td>
                                <td class="text-end">ACCIONES</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estudiantes as $est): ?>
                            <tr class="student-row">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-20 text-primary rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                                            <?= strtoupper(substr($est['nombre'], 0, 2)) ?>
                                        </div>
                                        <span class="fw-semibold text-white student-name"><?= $est['nombre'] ?></span>
                                    </div>
                                </td>
                                <td class="text-center student-user">@<?= $est['usuario'] ?></td>
                                <td class="text-center">
                                    <span class="badge rounded-pill <?= ($est['estado_hoy'] === 'Presente') ? 'badge-presente' : 'badge-ausente'; ?> px-3 py-2">
                                        <?= strtoupper($est['estado_hoy']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm text-info fs-5"><i class="bi bi-pencil-square"></i></button>
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
        <div class="modal-content border-0 shadow-lg bg-dark text-white rounded-5" style="border: 1px solid var(--primary) !important;">
            <div class="modal-body text-center p-5">
                <div class="mb-3 text-info"><i class="bi bi-broadcast fs-1"></i></div>
                <h4 class="fw-bold mb-3">Escaneo de Asistencia</h4>
                <div id="contenedorQR" class="mx-auto mb-4 p-3 bg-white rounded-4" style="width: fit-content;"></div>
                <p class="text-muted small mb-4">Los alumnos deben escanear para quedar registrados hoy.</p>
                <button class="btn btn-outline-danger w-100 rounded-pill py-3 fw-bold" data-bs-dismiss="modal">FINALIZAR</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    // 1. Buscador mejorado
    document.getElementById('studentSearch').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll('.student-row').forEach(row => {
            let name = row.querySelector('.student-name').innerText.toLowerCase();
            row.style.display = name.includes(val) ? '' : 'none';
        });
    });

    // 2. Persistencia de Tema
    function toggleTheme() {
        const body = document.documentElement;
        const icon = document.getElementById('themeIcon');
        let theme = body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        
        body.setAttribute('data-theme', theme);
        icon.className = theme === 'dark' ? 'bi bi-moon-stars' : 'bi bi-sun';
        localStorage.setItem('sga_theme', theme);
    }

    (function() {
        const savedTheme = localStorage.getItem('sga_theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        setTimeout(() => {
            const icon = document.getElementById('themeIcon');
            if(icon) icon.className = savedTheme === 'dark' ? 'bi bi-moon-stars' : 'bi bi-sun';
        }, 100);
    })();

    // 3. QR
    function generarQR() {
        const contenedor = document.getElementById("contenedorQR");
        contenedor.innerHTML = ""; 
        const url =
