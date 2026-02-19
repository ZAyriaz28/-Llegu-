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
$presentes = count(array_filter($estudiantes, fn($e) => $e['estado_hoy'] == 'Presente'));
$porcentaje_asistencia = ($total_alumnos > 0) ? round(($presentes / $total_alumnos) * 100) : 0;
?> 

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGA - Maestro Dashboard Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --primary-blue: #004a99;
            --tech-cyan: #00d4ff;
            --soft-bg: #f0f4f8;
            --glass-white: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.4);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
            min-height: 100vh;
            color: #2d3436;
            padding: 15px;
        }

        /* Contenedor Principal Estilo Apple/Glass */
        .main-wrapper {
            background: var(--glass-white);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            display: flex;
            min-height: 92vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
        }

        /* Sidebar Limpio */
        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.5);
            border-right: 1px solid rgba(0, 0, 0, 0.05);
            padding: 40px 25px;
        }

        .sidebar .nav-link {
            color: #636e72;
            padding: 14px 18px;
            border-radius: 16px;
            margin-bottom: 8px;
            font-weight: 500;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: white;
            color: var(--primary-blue);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.04);
            transform: translateX(5px);
        }

        .sidebar .nav-link i { font-size: 1.2rem; }

        /* Dashboard Content */
        .content-area {
            flex-grow: 1;
            padding: 45px;
            overflow-y: auto;
        }

        /* Tarjetas con Neumorfismo Suave */
        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 24px;
            border: 1px solid rgba(0, 0, 0, 0.02);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            transition: 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 74, 153, 0.1);
        }

        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 15px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 15px;
        }

        /* Tabla de Estudiantes Ultra-Limpia */
        .table-section {
            background: white;
            border-radius: 28px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.02);
        }

        .table thead th {
            background: #f8fafc;
            border: none;
            color: #94a3b8;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 15px;
        }

        .table tbody tr {
            transition: all 0.3s;
            border-bottom: 1px solid #f1f5f9;
        }

        .table tbody tr:hover {
            background: #f8fbff;
        }

        .table td { padding: 18px 15px; border: none; vertical-align: middle; }

        /* Badges Minimalistas */
        .badge-status {
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        .bg-presente { background: #e6fcf5; color: #0ca678; }
        .bg-ausente { background: #fff5f5; color: #fa5252; }

        /* Botón QR Destacado */
        .btn-action-qr {
            background: linear-gradient(135deg, var(--primary-blue), #007bff);
            color: white;
            border: none;
            border-radius: 16px;
            padding: 12px 25px;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0, 74, 153, 0.2);
            transition: 0.3s;
        }

        .btn-action-qr:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 30px rgba(0, 74, 153, 0.3);
            color: white;
        }

        /* Avatares */
        .student-avatar {
            width: 42px; height: 42px;
            background: #edf2f7;
            color: var(--primary-blue);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="main-wrapper animate__animated animate__fadeIn">
    <aside class="sidebar d-none d-lg-block">
        <div class="text-center mb-5">
            <div class="student-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 1.8rem; background: var(--primary-blue); color: white; border-radius: 25px;">
                <?php echo strtoupper(substr($nombre,0,1)); ?>
            </div>
            <h5 class="fw-bold mb-0"><?php echo $nombre; ?></h5>
            <span class="badge bg-light text-dark border mt-2">DOCENTE TÉCNICO</span>
        </div>
        
        <nav class="nav flex-column mt-4">
            <a class="nav-link active" href="#"><i class="bi bi-house-door-fill me-3"></i> Inicio</a>
            <a class="nav-link" href="#"><i class="bi bi-people-fill me-3"></i> Mis Alumnos</a>
            <a class="nav-link" href="historial_asistencias.php"><i class="bi bi-calendar-check-fill me-3"></i> Asistencias</a>
            <a class="nav-link" href="#"><i class="bi bi-file-earmark-text-fill me-3"></i> Calificaciones</a>
            <div style="margin-top: 100px;">
                <a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-left me-3"></i> Salir</a>
            </div>
        </nav>
    </aside>

    <main class="content-area">
        <header class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold mb-1">Centro de Mando</h2>
                <p class="text-muted mb-0">Gestión de Módulo: <strong>Técnico Superior A1</strong></p>
            </div>
            <div class="d-flex gap-3">
                <button class="btn btn-action-qr animate__animated animate__pulse animate__infinite" onclick="generarQR()">
                    <i class="bi bi-qr-code-scan me-2"></i> REGISTRO QR DE HOY
                </button>
            </div>
        </header>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-mortarboard"></i></div>
                    <p class="text-muted small mb-1">Total Grupo</p>
                    <h3 class="fw-bold mb-0"><?php echo $total_alumnos; ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-person-check"></i></div>
                    <p class="text-muted small mb-1">Presentes Hoy</p>
                    <h3 class="fw-bold mb-0"><?php echo $presentes; ?> <span class="fs-6 text-muted fw-normal">/ <?php echo $total_alumnos; ?></span></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-percent"></i></div>
                    <p class="text-muted small mb-1">% Asistencia</p>
                    <h3 class="fw-bold mb-0"><?php echo $porcentaje_asistencia; ?>%</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-graph-down-arrow"></i></div>
                    <p class="text-muted small mb-1">En Riesgo</p>
                    <h3 class="fw-bold mb-0">02</h3>
                </div>
            </div>
        </div>

        <div class="table-section animate__animated animate__fadeInUp">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0"><i class="bi bi-list-stars me-2 text-primary"></i> Seguimiento de Estudiantes</h5>
                <div class="search-box position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" class="form-control ps-5 border-0 bg-light rounded-3" style="width: 250px;" placeholder="Buscar alumno...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>ID Usuario</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $est): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="student-avatar me-3">
                                        <?php echo strtoupper(substr($est['nombre'], 0, 1) . substr(explode(" ", $est['nombre'])[1] ?? "", 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?php echo $est['nombre']; ?></div>
                                        <small class="text-muted">Inscrito en el curso</small>
                                    </div>
                                </div>
                            </td>
                            <td><code class="text-primary">@<?php echo $est['usuario']; ?></code></td>
                            <td class="text-center">
                                <span class="badge-status <?php echo $est['estado_hoy'] == 'Presente' ? 'bg-presente' : 'bg-ausente'; ?>">
                                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                                    <?php echo strtoupper($est['estado_hoy']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-light btn-sm rounded-3 me-1" title="Ver Perfil"><i class="bi bi-eye-fill"></i></button>
                                <button class="btn btn-light btn-sm rounded-3" title="Editar Nota"><i class="bi bi-pencil-square"></i></button>
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
        <div class="modal-content border-0" style="border-radius: 40px; overflow: hidden;">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <h4 class="fw-bold text-dark">Escanear para Asistencia</h4>
                    <p class="text-muted">El código se actualiza automáticamente</p>
                </div>
                <div id="contenedorQR" class="mx-auto p-4 bg-white rounded-5 shadow-sm mb-4" style="width: fit-content; border: 2px dashed #eee;"></div>
                <div class="alert alert-primary border-0 rounded-4 py-3">
                    <i class="bi bi-shield-lock-fill me-2"></i> Sesión Segura: <strong>A1-<?php echo date('His'); ?></strong>
                </div>
                <button class="btn btn-dark w-100 py-3 rounded-4 fw-bold mt-2" data-bs-dismiss="modal">FINALIZAR REGISTRO</button>
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
        new QRCode(contenedor, { text: url, width: 200, height: 200, colorDark : "#004a99", colorLight : "#ffffff" });
        new bootstrap.Modal(document.getElementById('modalAsistencia')).show();
    }
</script>
</body>
</html>
