<?php
session_start();
require_once "config/db.php";

/* Validar maestro */
if(!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "maestro"){
    header("Location: index.html");
    exit;
}

/* Obtener nombre */
//$nombre = $_SESSION["nombre"] ;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGA - Panel de Control Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style-global.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #004a99 0%, #007bff 100%);
            min-height: 100vh;
            padding: 10px;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 25px;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .sidebar-light {
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .nav-link {
            color: #495057;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            background: #004a99;
            color: white !important;
        }
        .card-custom {
            border: none;
            border-radius: 15px;
            transition: 0.3s;
        }
        .card-custom:hover { transform: translateY(-5px); }
        .table-container { height: 400px; overflow-y: auto; }
        
        /* Animación para el QR */
        .blink-animation { animation: blinker 2s linear infinite; }
        @keyframes blinker { 50% { opacity: 0.5; } }
    </style>
</head>
<body>

<div class="container-fluid glass-panel p-0">
    <div class="row g-0">
        <nav class="col-md-3 col-lg-2 sidebar-light p-4 d-none d-md-block">
            <div class="text-center mb-4">
                <div class="bg-primary text-white rounded-circle d-inline-block p-3 mb-2">
                    <i class="bi bi-person-badge fs-3"></i>
               </div>
    <h6 class="fw-bold mb-0"><?= $nombre; ?></h6>
    <small class="text-muted">Docente Técnico</small>
</div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-grid-fill me-2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-journal-check me-2"></i> Calificaciones</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-folder me-2"></i> Unidades</a></li>
                <li class="nav-item"><hr class="dropdown-divider"></li>
                <li class="nav-item"><a class="nav-link text-danger" href="index.html"><i class="bi bi-box-arrow-left me-2"></i> Salir</a></li>
            </ul>
        </nav>

        <main class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-primary"><i class="bi bi-mortarboard-fill me-2"></i>Gestión de Módulo</h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-dark btn-sm" onclick="generarQR()">
                        <i class="bi bi-qr-code-scan me-1"></i> QR de Hoy
                    </button>
                    <button class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i> Exportar</button>
                </div>
                <div class="card border-0 shadow-sm p-3 bg-white rounded-4 mb-3">
                    <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt-fill text-danger"></i> Perímetro de Seguridad</h6>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="checkGeo" checked>
                        <label class="form-check-label small" for="checkGeo">Restringir por ubicación (50m)</label>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted" style="font-size: 0.7rem;">
                            Ubicación actual: <strong>Centro Tecnológico</strong>
                        </small>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card card-custom bg-white p-3 shadow-sm border-start border-primary border-4">
                        <small class="text-muted fw-bold">PROGRESO MÓDULO</small>
                        <h4 class="fw-bold">65%</h4>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" style="width: 65%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom bg-white p-3 shadow-sm border-start border-success border-4">
                        <small class="text-muted fw-bold">ASISTENCIA PROMEDIO</small>
                        <h4 class="fw-bold">92%</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom bg-white p-3 shadow-sm border-start border-warning border-4">
                        <small class="text-muted fw-bold">PENDIENTES EVALUAR</small>
                        <h4 class="fw-bold">8</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom bg-white p-3 shadow-sm border-start border-danger border-4">
                        <small class="text-muted fw-bold">ALERTA DESERCIÓN</small>
                        <h4 class="fw-bold">2</h4>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-3 bg-white rounded-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold">Control de Grupo: <span class="text-primary">Técnico Superior A1</span></h6>
                            <div class="input-group input-group-sm w-50">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control bg-light border-start-0" placeholder="Buscar alumno...">
                            </div>
                        </div>
                        <div class="table-container">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr class="small">
                                        <th>ESTUDIANTE</th>
                                        <th class="text-center">ASIST.</th>
                                        <th class="text-center">NOTA ACUM.</th>
                                        <th class="text-center">ESTADO</th>
                                        <th class="text-center">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-info-subtle rounded-circle p-2 me-2 text-info">MA</div>
                                                <div>
                                                    <div class="fw-bold mb-0" style="font-size: 0.85rem;">Maria Alvarado</div>
                                                    <small class="text-muted" style="font-size: 0.7rem;">Carnet: 2024-001</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center small">12/12</td>
                                        <td class="text-center fw-bold text-success">95</td>
                                        <td class="text-center"><span class="badge bg-success-subtle text-success border border-success-subtle">Presente</span></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary" title="Ver Notas"><i class="bi bi-graph-up"></i></button>
                                            <button class="btn btn-sm btn-outline-warning" title="Justificar"><i class="bi bi-chat-text"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-3 bg-white rounded-4 mb-3 text-center">
                        <h6 class="fw-bold mb-3">Asistencia Rápida</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary py-3 fw-bold rounded-3" onclick="generarQR()">
                                <i class="bi bi-broadcast me-2"></i> ABRIR REGISTRO QR
                            </button>
                            <button class="btn btn-outline-secondary">
                                <i class="bi bi-clock-history me-1"></i> Ver Sesiones Anteriores
                            </button>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm p-3 bg-white rounded-4">
                        <h6 class="fw-bold mb-3">Avisos al Grupo</h6>
                        <textarea class="form-control form-control-sm mb-2" rows="3" placeholder="Escribir mensaje para los alumnos..."></textarea>
                        <button class="btn btn-dark btn-sm w-100">Publicar en el Muro</button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="modalAsistencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4" style="border-radius: 20px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-primary w-100">Escanea para Asistencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <div class="modal-body d-flex flex-column align-items-center justify-content-center">
                <div id="contenedorQR" class="mb-3 p-3 border rounded bg-light d-flex justify-content-center align-items-center" style="min-height: 220px; min-width: 220px;"></div>
                
                <h6 class="fw-bold text-dark mb-1">Técnico Superior A1</h6>
                <p class="text-muted small mb-0" id="fechaQR">Generando...</p>
                
                <div class="mt-3 badge bg-danger bg-opacity-10 text-danger border border-danger blink-animation">
                    <i class="bi bi-broadcast"></i> EMITIENDO SEÑAL
                </div>
            </div>

            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Finalizar Sesión
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    function generarQR() {
        console.log("Iniciando generación de QR...");

        // 1. Obtener contenedor y limpiarlo
        const contenedor = document.getElementById("contenedorQR");
        if(!contenedor) {
            alert("Error: No se encontró el contenedor del QR");
            return;
        }
        contenedor.innerHTML = ""; 

        // 2. Detectar ruta base (localhost o dominio)
        let baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
        
        // 3. Preparar datos
        const idClase = "Tecnico-A1"; 
        const fecha = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
        
        // 4. Crear URL de asistencia
        const urlAsistencia = `${baseUrl}/procesar_qr.php?clase=${idClase}&fecha=${fecha}`;
        console.log("URL Generada:", urlAsistencia);

        // 5. Actualizar fecha visible
        document.getElementById("fechaQR").innerText = new Date().toLocaleString();

        // 6. Generar QR
        try {
            new QRCode(contenedor, {
                text: urlAsistencia,
                width: 200,
                height: 200,
                colorDark : "#004a99",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M
            });
        } catch (e) {
            console.error("Error librería QR:", e);
            contenedor.innerHTML = "<p class='text-danger'>Error al cargar librería QR. Revisa tu conexión a internet.</p>";
        }

        // 7. Mostrar Modal
        const modalEl = document.getElementById('modalAsistencia');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>

</body>
</html>
