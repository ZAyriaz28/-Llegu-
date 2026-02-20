<?php
require_once "config/auth.php";

/* Si hay sesión restaurada o activa */
if (isset($_SESSION["id"])) {
    switch ($_SESSION["rol"]) {
        case "admin":
            header("Location: admin.php");
            break;
        case "maestro":
            header("Location: dashboard.php");
            break;
        case "estudiante":
            header("Location: estudiante.php");
            break;
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema Educativo - INATEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style-global.css">
    
  <style>
    :root {
        --primary-blue: #004a99;
        --tech-cyan: #00d4ff;
        --bg-gradient: radial-gradient(circle at top right, #002f61, #000b1a);
        --glass-bg: rgba(255, 255, 255, 0.12);
        --glass-border: rgba(255, 255, 255, 0.2);
        --text-main: #ffffff;
        --text-muted: rgba(255, 255, 255, 0.7);
        --input-bg: rgba(255, 255, 255, 0.05);
        --shadow-color: rgba(0, 0, 0, 0.4);
    }

    [data-theme="light"] {
        --bg-gradient: radial-gradient(circle at top right, #e0eafc, #cfdef3);
        --glass-bg: rgba(255, 255, 255, 0.6);
        --glass-border: rgba(255, 255, 255, 0.4);
        --text-main: #1a2a3a;
        --text-muted: #5a6a7a;
        --input-bg: rgba(255, 255, 255, 0.8);
        --shadow-color: rgba(0, 0, 0, 0.1);
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: var(--bg-gradient);
        min-height: 100vh;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow-y: auto; 
        box-sizing: border-box;
        transition: background 0.5s ease;
    }

    body::before {
        content: "";
        position: absolute;
        width: 300px;
        height: 300px;
        background: var(--tech-cyan);
        filter: blur(150px);
        opacity: 0.15;
        top: 10%;
        left: 10%;
        z-index: -1;
    }

    .theme-switch-wrapper {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }

    .btn-theme {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        color: var(--text-main);
        width: 40px; /* Más pequeño */
        height: 40px; /* Más pequeño */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        cursor: pointer;
        backdrop-filter: blur(10px);
        transition: all 0.4s ease;
        box-shadow: 0 8px 32px var(--shadow-color);
    }

    .btn-theme:hover {
        transform: scale(1.1) rotate(15deg);
        border-color: var(--tech-cyan);
    }

    /* TARJETA ESCALADA AL 80% */
    .login-container {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        padding: 1.5rem; /* Menos padding general */
        border-radius: 20px;
        box-shadow: 0 20px 40px var(--shadow-color);
        width: 95%;
        max-width: 340px; /* Ancho imitando el zoom */
        color: var(--text-main);
        margin: auto;
    }

    .inatec-logo {
        font-weight: 600;
        font-size: 1.4rem; /* Texto más pequeño */
        text-align: center;
        background: linear-gradient(to right, var(--text-main), var(--tech-cyan));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0;
    }

    .sub-title {
        text-align: center;
        color: var(--text-muted);
        font-size: 0.7rem; /* Texto más pequeño */
        margin-bottom: 1rem; /* Menos separación */
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .form-label {
        color: var(--text-main);
        font-size: 0.75rem; /* Texto más pequeño */
        margin-left: 5px;
        margin-bottom: 0.2rem;
        opacity: 0.9;
    }

    .form-control, .form-select {
        background: var(--input-bg);
        border: 1px solid var(--glass-border);
        border-radius: 10px;
        padding: 0.5rem 0.8rem; /* Cajas menos altas */
        font-size: 0.85rem; /* Texto interno más pequeño */
        color: var(--text-main) !important;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        background: var(--glass-bg);
        border-color: var(--tech-cyan);
        box-shadow: 0 0 10px rgba(0, 212, 255, 0.25);
    }

    .form-control::placeholder {
        color: var(--text-muted);
        opacity: 0.5;
    }

    /* ACERCANDO LOS ELEMENTOS ENTRE SÍ */
    .mb-3 { margin-bottom: 0.6rem !important; }
    .mb-4 { margin-bottom: 0.8rem !important; }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-blue) 0%, #007bff 100%);
        border: none;
        border-radius: 10px;
        padding: 0.6rem; /* Botón menos alto */
        font-weight: 600;
        font-size: 0.85rem;
        margin-top: 0;
        box-shadow: 0 8px 15px rgba(0, 74, 153, 0.3);
        transition: 0.3s;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 123, 255, 0.4);
        background: linear-gradient(135deg, #0056b3 0%, var(--tech-cyan) 100%);
    }

    .password-wrapper { position: relative; }
    
    .toggle-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: none;
        color: var(--text-muted);
        transition: 0.3s;
        padding: 0;
        font-size: 0.9rem;
    }

    .footer-text {
        text-align: center;
        margin-top: 1rem;
        font-size: 0.65rem; /* Letra bien pequeña */
        color: var(--text-muted);
        opacity: 0.6;
    }

    option { background: white; color: black; }
    [data-theme="dark"] option { background: #001a33; color: white; }

    .login-error-alert {
        background: rgba(220, 53, 69, 0.2);
        border: 1px solid rgba(220, 53, 69, 0.3);
        color: #ff99a2;
        padding: 6px 10px; /* Alerta menos alta */
        border-radius: 8px;
        font-size: 0.75rem;
        margin-bottom: 0.8rem;
    }
    
</style>


</head>

<body>
    <div class="theme-switch-wrapper animate__animated animate__fadeIn">
        <button id="theme-toggle" class="btn-theme">
            <i id="theme-icon" class="bi bi-moon-stars-fill"></i>
        </button>
    </div>

    <div class="login-container animate__animated animate__fadeInUp">
        <div class="inatec-logo">Sistema Educativo</div>
        <div class="sub-title">Tecnológico Nacional</div>

        <?php if (isset($_SESSION["error_login"])): ?>
            <div class="login-error-alert animate__animated animate__shakeX">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= $_SESSION["error_login"]; ?>
            </div>
            <?php unset($_SESSION["error_login"]); ?>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="user" class="form-label">Usuario o Correo</label>
                <input type="text" class="form-control" id="user" name="user" placeholder="nombre.apellido" required>
            </div>

            <div class="mb-3">
                <label for="pass" class="form-label">Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="pass" name="pass" placeholder="••••••••" required>
                    <button type="button" class="toggle-icon" id="btnToggle">
                        <i id="icono" class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Tipo de Usuario</label>
                <select class="form-select" name="rol" required>
                    <option value="estudiante">Estudiante</option>
                    <option value="maestro">Docente / Maestro</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>

            <div class="mb-4 d-flex align-items-center justify-content-between">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="recordar" name="recordar">
                    <label class="form-check-label small" for="recordar" style="color: var(--text-muted);">Recordarme</label>
                </div>
                <a href="recordarme.php" class="small text-decoration-none" style="color: var(--tech-cyan);">¿Qué es esto?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100">Entrar al Sistema</button>

            <div class="text-center mt-3">
                <span class="small" style="color: var(--text-muted);">¿Nuevo aquí?</span>
                <a href="crear-cuenta.html" class="text-decoration-none small fw-bold ms-1" style="color: var(--tech-cyan);">Crear Cuenta</a>
            </div>
        </form>

        <div class="footer-text">© 2026 Nicaragua - Educación Técnica</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="viewpassword.js"></script>
    <script src="theme-loader.js"></script>
</body>
</html>
