<?php
require_once "config/auth.php";
require_once "config/db.php"; 

if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "maestro") {
    header("Location: /index.php");
    exit();
}

$nombre = $_SESSION["nombre"];
$hoy = date('Y-m-d');

// Consulta optimizada
$sql = "SELECT u.id, u.nombre, u.usuario, a.fecha AS fecha_asistencia,
        CASE WHEN a.id IS NOT NULL THEN 'Presente' ELSE 'Ausente' END AS estado_hoy
        FROM usuarios u
        LEFT JOIN asistencias a ON u.id = a.usuario_id AND a.fecha = :hoy
        WHERE u.rol_id = 3 ORDER BY u.nombre ASC";

$stmt = $db->prepare($sql);
$stmt->execute([':hoy' => $hoy]);
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_alumnos = count($estudiantes);
$presentes = count(array_filter($estudiantes, fn($e) => $e['estado_hoy'] == 'Presente'));
$porcentaje_asistencia = ($total_alumnos > 0) ? round(($presentes / $total_alumnos) * 100) : 0;
?> 

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGA - Maestro Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --primary-blue: #004a99;
            --tech-cyan: #00d4ff;
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at top right, #002f61, #000b1a);
            min-height: 100vh;
            color: white;
            padding: 20px;
        }

        /* Contenedor Principal Glass */
        .main-wrapper {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            display: flex;
            min-height: 90vh;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        /* Sidebar Pro */
        .sidebar {
            width: 260px;
            background: rgba(0, 0, 0, 0.2);
            border-right: 1px solid var(--glass-border);
            padding: 30px 20px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.6);
            padding: 12px 15px;
            border-radius: 15px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            transition: 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            background: linear-gradient(90deg, var(--primary-blue), var(--tech-cyan));
            color: white;
            box-shadow: 0 10px 20px rgba(0, 212, 255, 0.2);
        }

        /* Tarjetas de Estadísticas */
        .stat-card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 20px;
            transition: 0.3s;
        }

        .stat-card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        /* Tabla Estilizada */
        .custom-table-card {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            padding: 20px;
        }

        .table { color: white; border-collapse: separate; border-spacing: 0 10px; }
        .table thead th { border: none; color: rgba(255,255,255,0.5); font-weight: 400; font-size: 0.8rem; }
        .table tbody tr { background: rgba(255,255,255,0.03); transition: 0.3s; }
        .table tbody tr td { border: none; padding: 15px 10px; vertical-align: middle; }
        .table tbody tr:hover { background: rgba(255,255,255,0.08); transform: scale(1.01); }
        .table tbody tr td:first-child { border-radius: 15px 0 0 15px; }
        .table tbody tr td:last-child { border-radius: 0 15px 15px 0; }

        /* Badge Glow */
        .badge-presente { background: rgba(40, 167, 69, 0.2); color: #2ecc71; border: 1px solid #2ecc71; }
        .badge-ausente { background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid #e74c3c; }

        .btn-qr {
            background: linear-gradient(135deg, #ff9966, #ff5e62);
            border: none;
            font-weight: 600;
            border-radius: 12px;
            padding: 10px 20px;
        }
        
        .avatar-circle {
            width: 40px; height: 40px;
            background: linear-gradient(45deg, var(--primary-blue), var(--tech-cyan));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; font-weight: 600;
        }
    </style>
</head>
<body>

<div class="main-wrapper animate__animated animate__fadeIn">
    <aside class="sidebar d-none d-lg-block">
        <div class="text-center mb-5">
            <div class="avatar-circle mx-auto mb-3" style="width: 70px; height: 70px; font-size: 1.5rem;">
                <?php echo strtoupper(substr($nombre,0,1)); ?>
            </div>
            <h6 class="mb-0 fw-bold"><?php echo $nombre; ?></h6>
            <small class="text-white-50">Docente Principal</small>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link active" href="#"><i class="bi bi-grid-1x2-fill me-3"></i> Dashboard</a>
            <a class="nav-link" href="#"><i class="bi bi-person-video3 me-3"></i> Mis Clases</a>
            <a class="nav-link" href="historial_asistencias.php"><i class="bi bi-file-earmark-bar-graph me-3"></i> Reportes</a>
            <a class="nav-link" href="#"><i class="bi bi-gear me-3"></i> Configuración</a>
            <div class="mt-5">
                <a class="nav-link text-danger" href="logout.php"><i class="bi bi-power me-3"></i> Cerrar Sesión</a>
            </div>
        </nav>
    </aside>

    <main class="flex-grow-1 p-4 p-lg-5" style="overflow-y: auto;">
        <header class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="fw-bold mb-1">Bienvenido, Prof. <?php echo explode(" ", $nombre)[0]; ?></h3>
                <p class="text-white-50 mb-0">Gestión de asistencia para hoy: <?php echo date('d M, Y'); ?></p>
            </div>
            <button class="btn btn-qr animate__animated animate__pulse animate__infinite" onclick="generarQR()">
                <i class="bi bi-qr-code-scan me-2"></i> INICIAR REGISTRO QR
            </button>
        </header>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary bg-opacity-25 text-primary"><i class="bi bi-people"></i></div>
                    <small class="text-white-50">Total Estudiantes</small>
                    <h3 class="fw-bold mb-0"><?php echo $total_alumnos; ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success bg-opacity-25 text-success"><i class="bi bi-check2-circle"></i></div>
                    <small class="text-white-50">Asistencia Hoy</small>
                    <h3 class="fw-bold mb-0"><?php echo $porcentaje_asistencia; ?>%</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning bg-opacity-25 text-warning"><i class="bi bi-clock-history"></i></div>
                    <small class="text-white-50">Llegadas Tarde</small>
                    <h3 class="fw-bold mb-0">5</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card border-danger border-opacity-50">
                    <div class="stat-icon bg-danger bg-opacity-25 text-danger"><i class="bi bi-exclamation-triangle"></i></div>
                    <small class="text-white-50">Alerta Deserción</small>
                    <h3 class="fw-bold mb-0">2</h3>
                </div>
            </div>
        </div>

        <div class="custom-table-card animate__animated animate__fadeInUp">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Lista de Grupo: <span class="text-info">Técnico A1</span></h5>
                <div class="input-group w-25">
                    <span class="input-group-text bg-transparent border-0 text-white-50"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control bg-transparent border-0 text-white" placeholder="Filtrar...">
                </div>
            </div>

            <div class="table-responsive" style="max-height: 500px;">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ESTUDIANTE</th>
                            <th>USUARIO</th>
                            <th class="text-center">ESTADO HOY</th>
                            <th class="text-center">RENDIMIENTO</th>
                            <th class="text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $est): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3"><?php echo substr($est['nombre'],0,2); ?></div>
                                    <span class="fw-medium"><?php echo $est['nombre']; ?></span>
                                </div>
                            </td>
                            <td><span class="text-white-50 small">@<?php echo $est['usuario']; ?></span></td>
                            <td class="text-center">
                                <span class="badge rounded-pill <?php echo $est['estado_hoy'] == 'Presente' ? 'badge-presente' : 'badge-ausente'; ?> px-3 py-2">
                                    <?php echo $est['estado_hoy']; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="progress bg-white bg-opacity-10" style="height: 6px; width: 100px; margin: 0 auto;">
                                    <div class="progress-bar bg-info" style="width: 85%"></div>
                                </div>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-light border-0"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-outline-light border-0"><i class="bi bi-pencil-square"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="modalAsistencia" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="background: #fff; border-radius: 30px;">
            <div class="modal-body text-center p-5">
                <h4 class="fw-bold text-dark mb-4">Registro de Asistencia</h4>
                <div id="contenedorQR" class="mx-auto mb-4 p-3 bg-white shadow-sm rounded-4" style="width: fit-content;"></div>
                <p class="text-muted small">Los estudiantes deben escanear este código para marcar su entrada.</p>
                <div class="badge bg-primary bg-opacity-10 text-primary p-2 px-3">
                    <i class="bi bi-clock me-2"></i> Sesión activa: Técnico Superior A1
                </div>
                <button class="btn btn-dark w-100 mt-4 py-3 rounded-4 fw-bold" data-bs-dismiss="modal">CERRAR REGISTRO</button>
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
        let url = window.location.origin + "/procesar_qr.php?clase=A1&fecha=<?php echo $hoy; ?>";
        new QRCode(contenedor, { text: url, width: 220, height: 220, colorDark : "#001a33" });
        new bootstrap.Modal(document.getElementById('modalAsistencia')).show();
    }
</script>
</body>
</html>
