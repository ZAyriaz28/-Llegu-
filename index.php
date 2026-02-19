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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style-global.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #004a99 0%, #007bff 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 95%;
            max-width: 420px;
        }

        .inatec-logo {
            color: #004a99;
            font-weight: 600;
            font-size: 1.8rem;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .sub-title {
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        /* Estilo para la Alerta de Error Decorada */
        .login-error-alert {
            animation: shake 0.5s ease-in-out;
            border-radius: 12px;
            background: rgba(220, 53, 69, 0.1);
            border: none;
            color: #842029;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 1.5rem;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
        }

        .btn-primary {
            background-color: #004a99;
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #003366;
            transform: translateY(-2px);
        }

        .footer-text {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .password-wrapper .form-control {
    padding-right: 45px;
}

.toggle-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: none;
    color: #6c757d; /* Color gris original */
    cursor: pointer;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.3s ease; /* Transición suave de color */
    outline: none !important;
    box-shadow: none !important;
}

/* Esta clase mantendrá el azul cuando el ojo esté "tachado" */
.toggle-icon.active-blue {
    color: #007bff !important; /* Azul de INATEC */
}

.toggle-icon:focus {
    outline: none;
}

    </style>
</head>

<body>
    <div class="login-container">
        <div class="inatec-logo">Sistema Educativo</div>
        <div class="sub-title">Tecnológico Nacional - Nicaragua</div>

        <?php if (isset($_SESSION["error_login"])): ?>
            <div class="login-error-alert">
                <i class="bi bi-exclamation-circle-fill fs-5 me-2"></i>
                <div>
                    <strong>Error:</strong> <?= $_SESSION["error_login"]; ?>
                </div>
            </div>
            <?php unset($_SESSION["error_login"]); ?>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="user" class="form-label text-dark fw-medium">Usuario o Correo</label>
                <input type="text" class="form-control" id="user" name="user" placeholder="nombre.apellido" required>
            </div>

            <div class="mb-3">
                <label for="pass" class="form-label text-dark fw-medium">Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="pass" name="pass" placeholder="••••••••" required>
                    <button type="button" class="toggle-icon" id="btnToggle">
                        <i id="icono" class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-dark fw-medium">Tipo de Usuario</label>
                <select class="form-select" name="rol" required>
                    <option value="estudiante">Estudiante</option>
                    <option value="maestro">Docente / Maestro</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>

            <div class="mb-3 d-flex align-items-center justify-content-between">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="recordar" name="recordar" value="1">
                    <label class="form-check-label fw-medium text-dark" for="recordar">Recordarme</label>
                </div>
                <a href="recordarme.php" class="small text-decoration-none fw-semibold text-primary">¿Qué es esto?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">Entrar al Sistema</button>

            <div class="text-center">
                <span class="small text-muted">¿No Tienes Cuenta?</span>
                <a href="crear-cuenta.html" class="text-decoration-none small fw-bold text-primary ms-1">Crear Cuenta</a>
            </div>
        </form>

        <div class="footer-text">© 2026 Nicaragua - Educación Técnica</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="viewpassword.js"></script>
</body>
</html>
