<?php
require_once "config/auth.php";
require_once "config/db.php";

/* Validar sesión y rol de maestro */
if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "maestro") {
    header("Location: index.php");
    exit();
}

$nombre_maestro = $_SESSION["nombre"];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGA - Historial Tech Edition</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
            background-attachment: fixed;
            min-height: 100vh;
            color: #ffffff;
            padding: 20px;
        }

        /* Panel Principal Glassmorphism */
        .main-container {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 30px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            padding: 40px;
            max-width: 1200px;
            margin: auto;
        }

        /* Tarjetas de Filtro */
        .filter-card {
            background: var(--glass-dark);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 25px;
        }

        /* Inputs Estilo Cyberpunk */
        .form-control, .input-group-text {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid var(--glass-border) !important;
            color: white !important;
            border-radius: 12px;
        }

        .form-control:focus {
            border-color: var(--tech-cyan) !important;
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.2);
        }

        /* Tablas Tecnológicas */
        .table-container {
            border-radius: 20px;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass-border);
        }

        .custom-table {
            color: white !important;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .custom-table thead th {
            background: transparent;
            color: var(--tech-cyan);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1.5px;
            border: none;
            padding: 20px;
        }

        .custom-table tbody tr {
            background: rgba(255, 255, 255, 0.02);
            transition: all 0.3s ease;
        }

        .custom-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: scale(1.005);
        }

        .custom-table td {
            padding: 15px 20px !important;
            vertical-align: middle;
            border: none !important;
        }

        /* Botones Neón */
        .btn-tech {
            border-radius: 12px;
            padding: 12px 25px;
            font-weight: 600;
            transition: 0.4s;
            border: none;
        }

        .btn-consultar {
            background: linear-gradient(135deg, #00d4ff, #004a99);
            color: white;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
        }

        .btn-excel {
            background: rgba(29, 111, 66, 0.2);
            color: #2ecc71;
            border: 1px solid #2ecc71;
        }

        .btn-excel:hover {
            background: #1D6F42;
            color: white;
            box-shadow: 0 0 20px rgba(46, 204, 113, 0.4);
        }

        /* Badge de asistencia */
        .badge-presente {
            background: rgba(0, 255, 128, 0.1) !important;
            color: #00ff80 !important;
            border: 1px solid #00ff80;
            text-shadow: 0 0 5px #00ff80;
        }

        .avatar-mini {
            background: linear-gradient(135deg, var(--primary-blue), var(--tech-cyan));
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
        }

        .bg-icon-tech {
            position: absolute;
            font-size: 8rem;
            color: rgba(0, 212, 255, 0.05);
            right: -20px;
            top: -20px;
            z-index: 0;
            pointer-events: none;
        }

        .blink-cyan { animation: blinker 2s infinite alternate; }
        @keyframes blinker { from { opacity: 1; } to { opacity: 0.4; } }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-thumb { background: var(--primary-blue); border-radius: 10px; }
    </style>
</head>
<body>

<div class="main-container position-relative animate__animated animate__fadeIn">
    <i class="bi bi-cpu bg-icon-tech"></i>
    
    <div class="d-flex justify-content-between align-items-center mb-5 position-relative">
        <div>
            <a href="dashboard.php" class="text-decoration-none small fw-bold text-info">
                <i class="bi bi-chevron-left"></i> VOLVER AL TERMINAL
            </a>
            <h2 class="fw-bold text-white mt-2 mb-0">Historial de <span class="text-info">Registros</span></h2>
            <p class="text-white-50 small">Operador: <?php echo htmlspecialchars($nombre_maestro); ?> | SGA-SECURE-ID</p>
        </div>
        <div class="rounded-4 d-flex align-items-center justify-content-center shadow-lg" 
             style="width: 70px; height: 70px; background: var(--glass-dark); border: 1px solid var(--tech-cyan);">
            <i class="bi bi-database-check text-info fs-2 blink-cyan"></i>
        </div>
    </div>

    <div class="card filter-card mb-5">
        <form id="formFiltro" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-info">RANGO INICIAL</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                    <input type="date" class="form-control shadow-none" id="fecha_inicio" required>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-info">RANGO FINAL</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                    <input type="date" class="form-control shadow-none" id="fecha_fin" required>
                </div>
            </div>
            <div class="col-md-4 d-flex gap-3">
                <button type="button" id="btnConsultar" class="btn btn-tech btn-consultar w-100">
                    <i class="bi bi-shield-shaded me-2"></i>EJECUTAR
                </button>
                <button type="button" id="btnExportar" class="btn btn-tech btn-excel">
                    <i class="bi bi-file-earmark-spreadsheet fs-5"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="table-container position-relative">
        <div class="table-responsive">
            <table class="table custom-table" id="tablaAsistencias">
                <thead>
                    <tr>
                        <th class="ps-4">Timestamp</th>
                        <th>Estudiante</th>
                        <th>ID Usuario</th>
                        <th>Módulo</th>
                        <th class="text-center">Hora</th>
                        <th class="pe-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="tabla-body">
                    </tbody>
            </table>
        </div>
        
        <div id="loading" class="text-center p-5 d-none">
            <div class="spinner-border text-info" role="status" style="width: 3rem; height: 3rem;"></div>
            <p class="text-info mt-3 fw-bold letter-spacing-1">SINCRONIZANDO BASE DE DATOS...</p>
        </div>

        <div id="no-data" class="text-center py-5">
            <i class="bi bi-cloud-slash fs-1 text-white-50"></i>
            <p class="text-white-50 mt-2">No se han detectado registros en el sector seleccionado.</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
document.getElementById('btnConsultar').addEventListener('click', function() {
    const inicio = document.getElementById('fecha_inicio').value;
    const fin = document.getElementById('fecha_fin').value;
    const tbody = document.getElementById('tabla-body');
    const loading = document.getElementById('loading');
    const noData = document.getElementById('no-data');

    if(!inicio || !fin) {
        alert("Acceso denegado: Seleccione parámetros de fecha.");
        return;
    }

    tbody.innerHTML = '';
    noData.classList.add('d-none');
    loading.classList.remove('d-none');

    const formData = new FormData();
    formData.append('inicio', inicio);
    formData.append('fin', fin);

    fetch('buscar_historial.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        loading.classList.add('d-none');
        
        if(data.error) {
            alert(data.error);
            return;
        }

        if(data.length === 0) {
            noData.classList.remove('d-none');
            return;
        }

        data.forEach((reg, index) => {
            const iniciales = reg.nombre.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
            const fila = `
                <tr class="animate__animated animate__fadeInUp" style="animation-delay: ${index * 0.05}s">
                    <td class="ps-4">
                        <span class="text-info fw-bold" style="font-family: monospace;">${reg.fecha}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-mini text-white rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold" style="width: 35px; height: 35px; font-size: 0.7rem;">
                                ${iniciales}
                            </div>
                            <span class="fw-semibold" style="color: #e0e0e0;">${reg.nombre}</span>
                        </div>
                    </td>
                    <td><code class="text-info opacity-75">@${reg.usuario}</code></td>
                    <td class="small text-white-50">${reg.clase}</td>
                    <td class="text-center fw-bold" style="color: var(--tech-cyan);">${reg.hora}</td>
                    <td class="pe-4 text-center">
                        <span class="badge badge-presente rounded-pill px-3 py-2">
                            <i class="bi bi-patch-check-fill me-1"></i> VERIFICADO
                        </span>
                    </td>
                </tr>
            `;
            tbody.innerHTML += fila;
        });
    })
    .catch(error => {
        loading.classList.add('d-none');
        alert("Error de enlace satelital (Servidor).");
    });
});

document.getElementById('btnExportar').addEventListener('click', function() {
    const table = document.getElementById("tablaAsistencias");
    if(table.rows.length <= 1) {
        alert("No hay paquetes de datos para exportar.");
        return;
    }
    const wb = XLSX.utils.table_to_book(table, {sheet: "Historial"});
    XLSX.writeFile(wb, `SGA_Report_${new Date().getTime()}.xlsx`);
});
</script>

</body>
</html>
