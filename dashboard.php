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
    <title>SGA - Panel de Control Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --primary-blue: #004a99;
            --tech-cyan: #00d4ff;
            --glass-bg: rgba(255, 255, 255, 0.96);
            --sidebar-bg: rgba(248, 249, 250, 0.85);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at bottom left, #002f61, #000b1a);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }

        /* Panel Principal Glassmorphism */
        .glass-panel {
            background: var(--glass-bg);
            border-radius: 30px;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeIn 0.8s ease-out;
        }

        /* Sidebar mejorado */
        .sidebar-light {
            background: var(--sidebar-bg);
            border-right: 1px solid rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(5px);
        }

        .nav-link {
            color: #555;
            padding: 12px 20px;
            border-radius: 14px;
            margin-bottom: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(0, 74, 153, 0.05);
            color: var(--primary-blue);
            transform: translateX(5px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #007bff 100%);
            color: white !important;
            box-shadow: 0 8px 15px rgba(0, 74, 153, 0.2);
        }

        /* Tarjetas Personalizadas */
        .card-custom {
            border: none;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 10px 20px rgba(0,0,0,0.03);
            transition: 0.3s;
            border: 1px solid #f0f0f0;
        }

        .card-custom:hover { 
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 74, 153, 0.1);
        }

        .border-primary { border-left: 5px solid var(--primary-blue) !important; }
        .border-success { border-left: 5px solid #28a745 !important; }
        .border-warning { border-left: 5px solid #ffc107 !important; }
        .border-danger { border-left: 5px solid #dc3545 !important; }

        /* Estilo de la Tabla */
        .table-container { height: 420px; overflow-y: auto; border-radius: 15px; }
        .table thead th {
            background-color: #f8f9fa;
            color: #888;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* Botones y Animaciones */
        .btn-primary { background: var(--primary-blue); border: none; border-radius: 10px; }
        .btn-dark { background: #1a1a1a; border-radius: 10px; }
        
        .blink-animation { animation: blinker 1.5s cubic-bezier(.5, 0, 1, 1) infinite alternate; }
        @keyframes blinker { from { opacity: 1; } to { opacity: 0.3; } }

        .progress { background-color: #e9ecef; border-radius: 10px; }
        .progress-bar { background: linear-gradient(to right, var(--primary-blue), var(--tech-cyan)); }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* Scrollbar suave para la tabla */
        .table-container::-webkit-scrollbar { width: 6px; }
        .table-container::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
    </style>
</head>
<body>

<div class="container-fluid glass-panel p-0 animate__animated animate__fadeIn">
    <div class="row g-0">
        <nav class="col-md-3 col-lg-2 sidebar-light p-4 d-none d-md-block">
            <div class="text-center mb-5">
                <div class="rounded-circle d-inline-block p-1 mb-3 shadow-sm" style="background: linear-gradient(135deg, var(--primary-blue), var(--tech-cyan));">
                    <div class="bg-white rounded-circle p-3">
                        <i class="bi bi-person-badge fs-3 text-primary"></i>
                    </div>
                </div>
                <h6 class="fw-bold mb-0 text-dark"><?php echo $nombre; ?></h6>
                <small class="text-muted text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Docente Técnico</small>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-grid-fill me-2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-journal-check me-2"></i> Calificaciones</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-folder me-2"></i> Unidades</a></li>
                <li class="nav-item"><hr class="dropdown-divider opacity-10"></li>
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-left me-2"></i> Salir</a></li>
            </ul>
        </nav>

        <main class="col-md-9 col-lg-10 p-4 bg-white bg-opacity-50">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold m-0" style="color: var(--primary-blue)">
                    <i class="bi bi-mortarboard-fill me-2 text-info"></i>Gestión de Módulo
                </h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-dark btn-sm px-3" onclick="generarQR()">
                        <i class="bi bi-qr-code-scan me-1"></i> QR DE HOY
                    </button>
                    <a href="#" class="btn btn-success btn-sm px-3"><i class="bi bi-file-earmark-excel me-1"></i> EXPORTAR</a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card card-custom p-3 animate__animated animate__fadeInDown">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Perímetro de Seguridad</h6>
                                <small class="text-muted">Ubicación: <strong>Centro Tecnológico</strong></small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="checkGeo" checked style="cursor:pointer; width: 3em; height: 1.5em;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-custom p-3 border-primary">
                        <small class="text-muted fw-bold">ASISTENCIA HOY</small>
                        <h4 class="fw-bold my-1"><?php echo $porcentaje_asistencia; ?>%</h4>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar" style="width: <?php echo $porcentaje_asistencia; ?>%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-success">
                        <small class="text-muted fw-bold">PRESENTES</small>
                        <h4 class="fw-bold my-1 text-success"><?php echo $presentes; ?> <span class="text-muted fw-light fs-6">/ <?php echo $total_alumnos; ?></span></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-warning">
                        <small class="text-muted fw-bold">PENDIENTES</small>
                        <h4 class="fw-bold my-1 text-warning">8</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-danger">
                        <small class="text-muted fw-bold">EN RIESGO</small>
                        <h4 class="fw-bold my-1 text-danger">2</h4>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card card-custom p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold m-0 text-dark">Control de Grupo: <span class="text-primary">Técnico Superior A1</span></h6>
                            <div class="input-group input-group-sm w-50 shadow-sm rounded-pill overflow-hidden">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control bg-light border-0" placeholder="Filtrar estudiante...">
                            </div>
                        </div>
                        <div class="table-container">
                            <table class="table align-middle">
                                <thead>
                                    <tr class="small text-muted">
                                        <th>ESTUDIANTE</th>
                                        <th class="text-center">NOTA</th>
                                        <th class="text-center">ESTADO</th>
                                        <th class="text-center">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($estudiantes as $est): 
                                        $iniciales = strtoupper(substr($est['nombre'], 0, 1) . substr(explode(" ", $est['nombre'])[1] ?? "", 0, 1));
                                        $esPresente = ($est['estado_hoy'] === 'Presente');
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle me-3 text-white fw-bold shadow-sm" style="background: linear-gradient(135deg, var(--primary-blue), var(--tech-cyan)); width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                                        <?php echo $iniciales; ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-dark mb-0" style="font-size: 0.85rem;"><?php echo htmlspecialchars($est['nombre']); ?></div>
                                                        <small class="text-muted" style="font-size: 0.7rem;">@<?php echo htmlspecialchars($est['usuario']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center fw-bold text-dark">85%</td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill <?php echo $esPresente ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> px-3 py-2 border <?php echo $esPresente ? 'border-success-subtle' : 'border-danger-subtle'; ?>">
                                                    <?php echo $esPresente ? 'Presente' : 'Ausente'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-light rounded-circle text-primary"><i class="bi bi-graph-up"></i></button>
                                                <button class="btn btn-sm btn-light rounded-circle text-warning"><i class="bi bi-chat-text"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card card-custom p-4 text-center mb-4">
                        <h6 class="fw-bold mb-3 text-dark">Asistencia Rápida</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary py-3 fw-bold rounded-4 shadow-sm" onclick="generarQR()">
                                <i class="bi bi-broadcast me-2 animate__animated animate__pulse animate__infinite"></i> ABRIR REGISTRO QR
                            </button>
                            <button class="btn btn-outline-secondary border-0 small text-muted">Ver Historial</button>
                        </div>
                    </div>

                    <div class="card card-custom p-4">
                        <h6 class="fw-bold mb-3 text-dark">Avisos al Grupo</h6>
                        <textarea class="form-control border-light bg-light mb-3" rows="3" placeholder="Mensaje para los alumnos..." style="border-radius: 12px; font-size: 0.9rem;"></textarea>
                        <button class="btn btn-dark w-100 rounded-3">Publicar en el Muro</button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="modalAsistencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 35px; background: rgba(255,255,255,0.98);">
            <div class="modal-body text-center p-5">
                <h4 class="fw-bold text-dark mb-4">Registro de Asistencia</h4>
                <div id="contenedorQR" class="mx-auto mb-4 p-4 bg-white rounded-4 shadow-sm border" style="width: fit-content;"></div>
                <div class="badge bg-primary bg-opacity-10 text-primary p-2 px-3 border border-primary-subtle mb-3">
                    Técnico Superior A1
                </div>
                <p class="text-muted small mb-4" id="fechaQR"></p>
                <div class="mt-2 badge bg-danger bg-opacity-10 text-danger border border-danger blink-animation py-2 px-4 rounded-pill">
                    <i class="bi bi-broadcast me-2"></i> EMITIENDO SEÑAL QR
                </div>
                <button type="button" class="btn btn-dark w-100 mt-5 py-3 rounded-4 fw-bold" data-bs-dismiss="modal">FINALIZAR SESIÓN</button>
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
        document.getElementById("fechaQR").innerText = new Date().toLocaleString();
        new QRCode(contenedor, { text: url, width: 220, height: 220, colorDark : "#004a99" });
        new bootstrap.Modal(document.getElementById('modalAsistencia')).show();
    }
</script>
</body>
</html>
