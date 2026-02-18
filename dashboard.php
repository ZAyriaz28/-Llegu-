<?php
session_start();
require_once "config/db.php";

/* ================== VALIDAR SESIÓN ================== */

if(!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "maestro"){
    header("Location: index.html");
    exit;
}

/* ================== DATOS DEL DOCENTE ================== */

$nombre = isset($_SESSION["nombre"]) 
    ? htmlspecialchars($_SESSION["nombre"]) 
    : "Docente";

?>

<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SGA - Panel de Control Docente</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- CSS Global -->
    <link rel="stylesheet" href="style-global.css">

    <!-- Estilos propios -->
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

        .nav-link:hover,
        .nav-link.active {
            background: #004a99;
            color: white !important;
        }

        .card-custom {
            border: none;
            border-radius: 15px;
            transition: 0.3s;
        }

        .card-custom:hover {
            transform: translateY(-5px);
        }

        .table-container {
            height: 400px;
            overflow-y: auto;
        }

        .blink-animation {
            animation: blinker 2s linear infinite;
        }

        @keyframes blinker {
            50% { opacity: 0.5; }
        }

    </style>

</head>

<body>

<div class="container-fluid glass-panel p-0">

    <div class="row g-0">

        <!-- ================== SIDEBAR ================== -->

        <nav class="col-md-3 col-lg-2 sidebar-light p-4 d-none d-md-block">

            <div class="text-center mb-4">

                <div class="bg-primary text-white rounded-circle d-inline-block p-3 mb-2">
                    <i class="bi bi-person-badge fs-3"></i>
                </div>

                <h6 class="fw-bold mb-0"><?= $nombre ?></h6>

                <small class="text-muted">Docente Técnico</small>

            </div>

            <ul class="nav flex-column">

                <li class="nav-item">
                    <a class="nav-link active" href="#">
                        <i class="bi bi-grid-fill me-2"></i> Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-journal-check me-2"></i> Calificaciones
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-folder me-2"></i> Unidades
                    </a>
                </li>

                <li class="nav-item"><hr></li>

                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="bi bi-box-arrow-left me-2"></i> Salir
                    </a>
                </li>

            </ul>

        </nav>


        <!-- ================== CONTENIDO ================== -->

        <main class="col-md-9 col-lg-10 p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h4 class="fw-bold text-primary">
                    <i class="bi bi-mortarboard-fill me-2"></i> Gestión de Módulo
                </h4>

                <div class="d-flex gap-2">

                    <button class="btn btn-dark btn-sm" onclick="generarQR()">
                        <i class="bi bi-qr-code-scan me-1"></i> QR de Hoy
                    </button>

                    <button class="btn btn-success btn-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i> Exportar
                    </button>

                </div>

            </div>


            <!-- ================== TARJETAS ================== -->

            <div class="row g-3 mb-4">

                <div class="col-md-3">
                    <div class="card card-custom bg-white p-3 shadow-sm border-start border-primary border-4">
                        <small class="text-muted fw-bold">PROGRESO</small>
                        <h4 class="fw-bold">65%</h4>
                        <div class="progress" style="height:6px">
                            <div class="progress-bar" style="width:65%"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-custom bg-white p-3 shadow-sm border-start border-success border-4">
                        <small class="text-muted fw-bold">ASISTENCIA</small>
                        <h4 class="fw-bold">92%</h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-custom bg-white p-3 shadow-sm border-start border-warning border-4">
                        <small class="text-muted fw-bold">PENDIENTES</small>
                        <h4 class="fw-bold">8</h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-custom bg-white p-3 shadow-sm border-start border-danger border-4">
                        <small class="text-muted fw-bold">ALERTAS</small>
                        <h4 class="fw-bold">2</h4>
                    </div>
                </div>

            </div>


            <!-- ================== TABLA ================== -->

            <div class="card border-0 shadow-sm p-3 bg-white rounded-4">

                <h6 class="fw-bold mb-3">
                    Control de Grupo: 
                    <span class="text-primary">Técnico Superior A1</span>
                </h6>

                <div class="table-container">

                    <table class="table align-middle">

                        <thead class="table-light">

                            <tr class="small">

                                <th>ESTUDIANTE</th>
                                <th class="text-center">ASIST.</th>
                                <th class="text-center">NOTA</th>
                                <th class="text-center">ESTADO</th>
                                <th class="text-center">ACCIONES</th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr>

                                <td>
                                    <div class="d-flex align-items-center">

                                        <div class="bg-info-subtle rounded-circle p-2 me-2 text-info">
                                            MA
                                        </div>

                                        <div>
                                            <div class="fw-bold">Maria Alvarado</div>
                                            <small class="text-muted">
                                                Carnet: 2024-001
                                            </small>
                                        </div>

                                    </div>
                                </td>

                                <td class="text-center">12/12</td>

                                <td class="text-center fw-bold text-success">95</td>

                                <td class="text-center">
                                    <span class="badge bg-success">
                                        Presente
                                    </span>
                                </td>

                                <td class="text-center">

                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-graph-up"></i>
                                    </button>

                                    <button class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-chat-text"></i>
                                    </button>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </main>

    </div>

</div>


<!-- ================== MODAL QR ================== -->

<div class="modal fade" id="modalAsistencia" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content text-center p-4 rounded-4">

            <div class="modal-header border-0">

                <h5 class="fw-bold text-primary w-100">
                    Escanea para Asistencia
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <div id="contenedorQR"
                     class="mb-3 p-3 border rounded bg-light"
                     style="min-height:220px"></div>

                <p class="small text-muted" id="fechaQR">Generando...</p>

            </div>

        </div>

    </div>

</div>


<!-- ================== SCRIPTS ================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>


<script>

function generarQR(){

    const contenedor = document.getElementById("contenedorQR");
    contenedor.innerHTML = "";

    const baseUrl = window.location.origin +
        window.location.pathname.substring(
            0,
            window.location.pathname.lastIndexOf('/')
        );

    const clase = "Tecnico-A1";
    const fecha = new Date().toISOString().split("T")[0];

    const url = `${baseUrl}/procesar_qr.php?clase=${clase}&fecha=${fecha}`;

    document.getElementById("fechaQR").innerText =
        new Date().toLocaleString();

    new QRCode(contenedor,{
        text: url,
        width:200,
        height:200
    });

    new bootstrap.Modal(
        document.getElementById("modalAsistencia")
    ).show();
}

</script>

</body>
</html>