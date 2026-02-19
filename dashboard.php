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
            --deep-navy: #000b1a;
            --glass-dark: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at top right, #002f61, #000b1a);
            min-height: 100vh;
            color: #ffffff;
            padding: 20px;
        }

        .glass-panel {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 30px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            border: 1px solid var(--glass-border);
            overflow: hidden;
        }

        .sidebar-light {
            background: rgba(0, 0, 0, 0.2);
            border-right: 1px solid var(--glass-border);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.6);
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 8px;
            transition: 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            background: linear-gradient(90deg, var(--primary-blue), var(--tech-cyan));
            color: white !important;
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.3);
        }

        .card-custom {
            background: var(--glass-dark);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 20px;
            transition: 0.3s;
        }

        /* --- SOLUCIÓN DEFINITIVA PARA EL BLANCO DE LA TABLA --- */
        .table-container { 
            height: 420px; 
            overflow-y: auto; 
            scrollbar-width: thin;
            scrollbar-color: var(--tech-cyan) transparent;
        }
        
        /* Eliminamos fondos de Bootstrap */
        .table { 
            --bs-table-bg: transparent !important;
            --bs-table-color: white !important;
            color: white !important;
            margin-bottom: 0; 
            border-collapse: separate; 
            border-spacing: 0 10px;
        }

        .table thead th {
            background: transparent !important;
            color: var(--tech-cyan) !important;
            font-size: 0.75rem;
            text-transform: uppercase;
            border: none;
            padding: 15px;
            letter-spacing: 1px;
        }

        /* Forzamos que las celdas NO sean blancas */
        .table td {
            background: transparent !important;
            border: none !important;
            padding: 15px !important;
            color: white !important;
        }

        .table tbody tr {
            background: rgba(255, 255, 255, 0.03) !important;
            transition: 0.3s;
            border-radius: 15px;
        }

        .table tbody tr:hover { 
            background: rgba(255, 255, 255, 0.08) !important;
            transform: scale(1.01);
        }

        /* Resalte del Nombre del Estudiante */
        .student-name {
            color: #ffffff !important;
            font-weight: 600 !important;
            font-size: 0.95rem;
            text-shadow: 0 0 8px rgba(255, 255, 255, 0.2);
        }

        .student-user {
            color: var(--tech-cyan) !important;
            font-size: 0.75rem;
            font-family: monospace;
            opacity: 0.8;
        }

        .avatar-tech {
            background: linear-gradient(135deg, var(--primary-blue), var(--tech-cyan));
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 0 10px rgba(0, 212, 255, 0.2);
        }

        /* Inputs Estilizados (para el buscador) */
        .form-control {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid var(--glass-border) !important;
            color: white !important;
            border-radius: 10px;
        }
        .form-control::placeholder { color: rgba(255,255,255,0.3); }

        .badge-presente { background: rgba(0, 255, 128, 0.15) !important; color: #00ff80 !important; border: 1px solid #00ff80; }
        .badge-ausente { background: rgba(255, 71, 87, 0.15) !important; color: #ff4757 !important; border: 1px solid #ff4757; }

        .btn-qr-neon {
            background: linear-gradient(135deg, #00d4ff, #004a99);
            color: white;
            border: none;
            font-weight: 600;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.4);
        }

        .blink-animation { animation: blinker 1.5s infinite alternate; }
        @keyframes blinker { from { opacity: 1; text-shadow: 0 0 10px var(--tech-cyan); } to { opacity: 0.5; } }
    </style>
</head>
<body>

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
                <small class="text-info" style="font-size: 0.7rem;">SISTEMA ACTIVO</small>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-layers me-2"></i> Módulos</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-graph-up-arrow me-2"></i> Reportes</a></li>
                <li class="nav-item mt-5"><a class="nav-link text-danger" href="logout.php"><i class="bi bi-power me-2"></i> Desconectar</a></li>
            </ul>
        </nav>

        <main class="col-md-9 col-lg-10 p-4">
            <header class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h3 class="fw-bold m-0 text-white">Gestión de <span class="text-info">Módulo</span></h3>
                    <p class="text-white-50 small mb-0">Terminal ID: SGA-MASTER-01</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-qr-neon btn-sm px-4 rounded-pill" onclick="generarQR()">
                        <i class="bi bi-qr-code-scan me-2"></i> INICIAR REGISTRO
                    </button>
                </div>
            </header>

            <div class="row g-3 mb-5">
                <div class="col-12">
                    <div class="card-custom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold m-0"><i class="bi bi-shield-lock text-info me-2"></i>Geofencing de Seguridad</h6>
                                <small class="text-white-50">Localización: Campus Central</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" checked style="cursor:pointer">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card-custom border-start border-info border-4">
                        <small class="text-white-50 fw-bold">ASISTENCIA</small>
                        <h3 class="fw-bold my-1 text-info"><?php echo $porcentaje_asistencia; ?>%</h3>
                        <div class="progress bg-dark" style="height: 4px;">
                            <div class="progress-bar" style="width: <?php echo $porcentaje_asistencia; ?>%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-custom border-start border-success border-4">
                        <small class="text-white-50 fw-bold">PRESENTES</small>
                        <h3 class="fw-bold my-1 text-success"><?php echo $presentes; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-custom border-start border-warning border-4">
                        <small class="text-white-50 fw-bold">TAREAS</small>
                        <h3 class="fw-bold my-1 text-warning">08</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-custom border-start border-danger border-4">
                        <small class="text-white-50 fw-bold">ALERTAS</small>
                        <h3 class="fw-bold my-1 text-danger">02</h3>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card-custom">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold m-0 text-info">Terminal de Estudiantes: A1</h6>
                            <div class="input-group input-group-sm w-50">
                                <input type="text" class="form-control" placeholder="Buscar por nombre o ID...">
                            </div>
                        </div>
                        
                        <div class="table-container">
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
                                                    <div class="rounded-circle me-3 avatar-tech">
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
                        <div class="d-grid gap-2">
                            <button class="btn btn-qr-neon py-3 fw-bold" onclick="generarQR()">
                                <i class="bi bi-broadcast me-2 blink-animation"></i> BROADCAST QR
                            </button>
                        </div>
                    </div>

                    <div class="card-custom">
                        <h6 class="fw-bold mb-3 text-info">Mensajería de Muro</h6>
                        <textarea class="form-control mb-3" rows="3" placeholder="Enviar aviso al grupo..."></textarea>
                        <button class="btn btn-outline-info w-100 btn-sm">PUBLICAR</button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="modalAsistencia" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 30px; background: #000b1a; border: 1px solid var(--tech-cyan) !important;">
            <div class="modal-body text-center p-5">
                <div class="mb-4 text-info blink-animation">
                    <i class="bi bi-qr-code fs-1"></i>
                    <h4 class="fw-bold mt-2">Sincronización QR</h4>
                </div>
                <div id="contenedorQR" class="mx-auto mb-4 p-3 bg-white rounded-4" style="width: fit-content;"></div>
                <div class="text-white-50 small mb-4">Escanee el código para validar su presencia.</div>
                <button class="btn btn-outline-danger w-100 py-3 rounded-pill fw-bold" data-bs-dismiss="modal">DETENER</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    function generarQR() {
        const contenedor = document.getElementById("contenedorQR");
        contenedor.innerHTML = ""; 
        const url = window.location.origin + "/procesar_qr.php?clase=A1&fecha=<?php echo $hoy; ?>";
        new QRCode(contenedor, { text: url, width: 220, height: 220, colorDark : "#000b1a" });
        new bootstrap.Modal(document.getElementById('modalAsistencia')).show();
    }
</script>
</body>
</html>
