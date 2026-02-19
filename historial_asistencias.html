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
    <title>SGA - Historial de Asistencias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-blue: #004a99;
            --accent-blue: #007bff;
            --glass-bg: rgba(255, 255, 255, 0.9);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--accent-blue) 100%);
            background-attachment: fixed;
            min-height: 100vh;
            color: #333;
            padding: 20px;
        }

        .main-container {
            background: var(--glass-bg);
            border-radius: 30px;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
            padding: 30px;
            max-width: 1200px;
            margin: auto;
            animation: slideUp 0.6s ease-out;
        }

        .filter-card {
            background: #ffffff;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        .custom-table thead {
            background: var(--primary-blue);
            color: white;
        }

        .custom-table tbody tr {
            transition: all 0.2s;
            border-bottom: 1px solid #eee;
            animation: fadeIn 0.4s ease forwards;
        }

        .custom-table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .btn-action {
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-excel {
            background: #1D6F42;
            color: white;
            border: none;
        }

        .btn-excel:hover {
            background: #155231;
            color: white;
            transform: translateY(-2px);
        }

        .bg-icon {
            position: absolute;
            font-size: 5rem;
            color: rgba(0,0,0,0.03);
            right: 20px;
            top: 20px;
            z-index: -1;
        }

        /* Animaciones */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .spinner-container {
            display: none;
            padding: 40px;
        }
    </style>
</head>
<body>

<div class="main-container position-relative">
    <i class="bi bi-clock-history bg-icon"></i>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="panel_profesor.php" class="text-decoration-none small fw-bold text-primary">
                <i class="bi bi-arrow-left"></i> Volver al Panel
            </a>
            <h2 class="fw-bold text-dark m-0">Historial de Registros</h2>
            <p class="text-muted small">Profesor: <?php echo htmlspecialchars($nombre_maestro); ?></p>
        </div>
        <div class="bg-primary rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px;">
            <i class="bi bi-qr-code-scan text-white fs-2"></i>
        </div>
    </div>

    <div class="card filter-card p-4 mb-4">
        <form id="formFiltro" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Fecha Inicio</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-event"></i></span>
                    <input type="date" class="form-control bg-light border-0 shadow-none" id="fecha_inicio" required>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Fecha Fin</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-check"></i></span>
                    <input type="date" class="form-control bg-light border-0 shadow-none" id="fecha_fin" required>
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="button" id="btnConsultar" class="btn btn-primary btn-action w-100 shadow-sm">
                    <i class="bi bi-search me-2"></i>Consultar
                </button>
                <button type="button" id="btnExportar" class="btn btn-excel btn-action w-100 shadow-sm">
                    <i class="bi bi-file-earmark-excel me-2"></i>Excel
                </button>
            </div>
        </form>
    </div>

    <div class="card filter-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table custom-table mb-0" id="tablaAsistencias">
                <thead>
                    <tr>
                        <th class="ps-4">Fecha</th>
                        <th>Estudiante</th>
                        <th>Usuario</th>
                        <th>Módulo</th>
                        <th class="text-center">Hora</th>
                        <th class="pe-4 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody id="tabla-body">
                    </tbody>
            </table>
        </div>
        
        <div id="loading" class="text-center spinner-container">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted mt-2 small">Buscando en los archivos...</p>
        </div>

        <div id="no-data" class="text-center py-5">
            <i class="bi bi-folder-x fs-1 text-muted"></i>
            <p class="text-muted mt-2">No hay registros para mostrar. Selecciona un rango de fechas.</p>
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
        alert("Por favor selecciona ambas fechas.");
        return;
    }

    // UI Feedback
    tbody.innerHTML = '';
    noData.classList.add('d-none');
    loading.style.display = 'block';

    // Petición al Backend
    const formData = new FormData();
    formData.append('inicio', inicio);
    formData.append('fin', fin);

    fetch('buscar_historial.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        loading.style.display = 'none';
        
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
                <tr style="animation-delay: ${index * 0.05}s">
                    <td class="ps-4 fw-bold">${reg.fecha}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; font-size: 0.75rem; font-weight: 600;">
                                ${iniciales}
                            </div>
                            <span class="small fw-semibold">${reg.nombre}</span>
                        </div>
                    </td>
                    <td class="text-muted small">@${reg.usuario}</td>
                    <td class="small">${reg.clase}</td>
                    <td class="text-center small">${reg.hora}</td>
                    <td class="pe-4 text-center">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Presente</span>
                    </td>
                </tr>
            `;
            tbody.innerHTML += fila;
        });
    })
    .catch(error => {
        loading.style.display = 'none';
        alert("Error de conexión con el servidor.");
    });
});

// Función para exportar a Excel profesionalmente
document.getElementById('btnExportar').addEventListener('click', function() {
    const table = document.getElementById("tablaAsistencias");
    if(table.rows.length <= 1) {
        alert("No hay datos para exportar.");
        return;
    }
    const wb = XLSX.utils.table_to_book(table, {sheet: "Asistencias"});
    XLSX.writeFile(wb, `Reporte_Asistencia_${new Date().toISOString().slice(0,10)}.xlsx`);
});
</script>

</body>
</html>
