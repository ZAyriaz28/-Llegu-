<?php

require_once "config/auth.php";
require_once "config/db.php"; 

if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "maestro") {
    header("Location: /index.php");
    exit();
}

$nombre = $_SESSION["nombre"];
$hoy = date('Y-m-d');

$sql = "SELECT u.id, u.nombre, u.usuario, a.fecha AS fecha_asistencia,
            CASE WHEN a.id IS NOT NULL THEN 'Presente' ELSE 'Ausente' END AS estado_hoy
        FROM usuarios u
        LEFT JOIN asistencias a ON u.id = a.usuario_id AND a.fecha = :hoy
        WHERE u.rol_id = 3 ORDER BY u.nombre ASC";

$stmt = $db->prepare($sql);
$stmt->execute([':hoy' => $hoy]);
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_alumnos = count($estudiantes);
$presentes = 0;
foreach($estudiantes as $e) { if($e['estado_hoy'] == 'Presente') $presentes++; }
$porcentaje_asistencia = ($total_alumnos > 0) ? round(($presentes / $total_alumnos) * 100) : 0;
?> 
   
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGA - Docente Tech Edition</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --primary-blue: #004a99;
            --tech-cyan: #00d4ff;
            /* Variables DARK (Default) */
            --bg-gradient: radial-gradient(circle at top right, #002f61, #000b1a);
            --panel-bg: rgba(0, 0, 0, 0.3);
            --sidebar-bg: rgba(0, 0, 0, 0.2);
            --card-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.6);
            --table-row-bg: rgba(255, 255, 255, 0.03);
            --shadow-color: rgba(0, 0, 0, 0.5);
        }

        /* --- MEJORA: MODO CLARO PREMIUM --- */
        [data-theme="light"] {
            --bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --panel-bg: rgba(255, 255, 255, 0.4);
            --sidebar-bg: rgba(255, 255, 255, 0.3);
            --card-bg: rgba(255, 255, 255, 0.8);
            --glass-border: rgba(255, 255, 255, 0.9);
            --text-main: #1a2a3a;
            --text-muted: #5a6a7a;
            --table-row-bg: #ffffff;
            --shadow-color: rgba(31, 38, 135, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-gradient) !important;
            background-attachment: fixed !important;
            min-height: 100vh;
            color: var(--text-main);
            padding: 20px;
            transition: all 0.5s ease;
        }

        .glass-panel {
            background: var(--panel-bg);
            border-radius: 30px;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            box-shadow: 0 10px 40px var(--shadow-color);
            border: 1px solid var(--glass-border);
            overflow: hidden;
        }

        .sidebar-light {
            background: var(--sidebar-bg);
            border-right: 1px solid var(--glass-border);
        }

        .nav-link {
            color: var(--text-muted);
            font-weight: 500;
        }

        .nav-link.active {
            background: linear-gradient(90deg, var(--primary-blue), var(--tech-cyan));
            color: white !important;
            box-shadow: 0 4px 15px rgba(0, 212, 255, 0.3);
        }

        .card-custom {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 4px 15px var(--shadow-color);
            transition: 0.3s;
        }

        /* Tablas con estilo de tarjetas flotantes */
        .table { border-spacing: 0 10px; border-collapse: separate; }
        .table thead th { color: var(--tech-cyan) !important; border: none; padding: 15px; }
        .table td { border: none; padding: 15px; color: var(--text-main); }

        .table tbody tr {
            background: var(--table-row-bg) !important;
            box-shadow: 0 2px 10px var(--shadow-color);
            border-radius: 12px;
            transition: 0.3s;
        }

        .table tbody tr:hover { transform: translateY(-2px); filter: brightness(1.02); }

        .student-name { color: var(--text-main) !important; font-weight: 600; }
        .student-user { color: var(--primary-blue); opacity: 0.7; font-weight: 500; }

        .avatar-tech {
            background: linear-gradient(135deg, var(--primary-blue), var(--tech-cyan));
            width: 38px; height: 38px;
            display: flex; align-items: center; justify-content: center;
            color: white; border-radius: 50%; font-weight: bold;
        }

        .btn-theme {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
            width: 45px; height: 45px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 12px var(--shadow-color);
            cursor: pointer;
        }

        .badge-presente { background: rgba(0, 255, 128, 0.15) !important; color: #00a854 !important; border: 1px solid #00ff80; }
        .badge-ausente { background: rgba(255, 71, 87, 0.15) !important; color: #d63031 !important; border: 1px solid #ff4757; }

        .btn-qr-neon {
            background: linear-gradient(135deg, #00d4ff, #004a99);
            color: white; border: none; font-weight: 600;
            box-shadow: 0 5px 20px rgba(0, 212, 255, 0.4);
        }
    </style>
</head>
<body>

<div class="theme-switch-wrapper animate__animated animate__fadeIn">
    <button id="theme-toggle" class="btn-theme">
        <i id="theme-icon" class="bi bi-moon-stars-fill"></i>
    </button>
</div>

<div class="container-fluid glass-panel p-0 animate__animated animate__fadeIn">
    <div class="row g-0">
        <nav class="col-md-3 col-lg-2 sidebar-light p-4 d-none d-md-block">
            <div class="text-center mb-5">
                <div class="rounded-circle d-inline-block p-1 mb-3" style="background: linear-gradient(135deg, var(--primary-blue), var(--tech-cyan));">
                    <div class="bg-dark rounded-circle p-3">
                        <i class="bi bi-cpu fs-3 text-info"></i>
                    </div>
                </div>
                <h6 class="fw-bold mb-0"><?php echo $nombre; ?></h6>
                <small class="text-info" style="font-size: 0.7rem;">MODO DOCENTE</small>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-layers me-2"></i> Módulos</a></li>
                <li class="nav-item"><a class="nav-link" href="historial_asistencias.php"><i class="bi bi-graph-up-arrow me-2"></i> Registros</a></li>
                <li class="nav-item mt-5"><a class="nav-link text-danger" href="logout.php"><i class="bi bi-power me-2"></i> Desconectar</a></li>
            </ul>
        </nav>

        <main class="col-md-9 col-lg-10 p-4">
            <header class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h3 class="fw-bold m-0">Gestión de <span class="text-info">Módulo</span></h3>
                    <p class="text-muted small mb-0">Terminal ID: SGA-MASTER-01</p>
                </div>
                <button class="btn btn-qr-neon btn-sm px-4 rounded-pill" onclick="generarQR()">
                    <i class="bi bi-qr-code-scan me-2"></i> INICIAR REGISTRO
                </button>
            </header>

            <div class="row g-3 mb-5">
                <div class="col-md-3">
                    <div class="card-custom border-start border-info border-4">
                        <small class="text-muted fw-bold">ASISTENCIA</small>
                        <h3 class="fw-bold my-1 text-info"><?php echo $porcentaje_asistencia; ?>%</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-custom border-start border-success border-4">
                        <small class="text-muted fw-bold">PRESENTES</small>
                        <h3 class="fw-bold my-1 text-success"><?php echo $presentes; ?></h3>
                    </div>
                </div>
                </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card-custom">
                        <h6 class="fw-bold mb-4 text-info">Listado de Estudiantes: A1</h6>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>ESTUDIANTE</th>
                                        <th class="text-center">ESTADO</th>
                                        <th class="text-center">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($estudiantes as $est): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-tech me-3">
                                                        <?php echo strtoupper(substr($est['nombre'], 0, 2)); ?>
                                                    </div>
                                                    <div>
                                                        <div class="student-name"><?php echo $est['nombre']; ?></div>
                                                        <div class="student-user">@<?php echo $est['usuario']; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill <?php echo ($est['estado_hoy'] === 'Presente') ? 'badge-presente' : 'badge-ausente'; ?> px-3 py-2">
                                                    <?php echo strtoupper($est['estado_hoy']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm text-info"><i class="bi bi-eye"></i></button>
                                                <button class="btn btn-sm text-warning"><i class="bi bi-pencil-square"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card-custom text-center mb-4">
                        <h6 class="fw-bold mb-3">Interacción Directa</h6>
                        <button class="btn btn-qr-neon w-100 py-3 fw-bold" onclick="generarQR()">
                            <i class="bi bi-broadcast me-2 blink-animation"></i> BROADCAST QR
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="modalAsistencia" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 30px; background: var(--card-bg); border: 1px solid var(--tech-cyan) !important; backdrop-filter: blur(20px);">
            <div class="modal-body text-center p-5">
                <div class="mb-4 text-info"><i class="bi bi-qr-code fs-1"></i></div>
                <div id="contenedorQR" class="mx-auto mb-4 p-3 bg-white rounded-4" style="width: fit-content; box-shadow: 0 10px 30px rgba(0,0,0,0.1);"></div>
                <button class="btn btn-outline-danger w-100 py-3 rounded-pill fw-bold" data-bs-dismiss="modal">CERRAR REGISTRO</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="theme-loader.js"></script>
<script>
    function generarQR() {
        const contenedor = document.getElementById("contenedorQR");
        contenedor.innerHTML = ""; 
        const url = window.location.origin + "/procesar_qr.php?clase=A1&fecha=<?php echo $hoy; ?>";
        new QRCode(contenedor, { 
            text: url, 
            width: 220, 
            height: 220, 
            colorDark : "#000b1a",
            colorLight : "#ffffff" 
        });
        new bootstrap.Modal(document.getElementById('modalAsistencia')).show();
    }
</script>
</body>
</html>
