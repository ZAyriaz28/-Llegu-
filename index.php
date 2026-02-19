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
    
    <style>
        :root {
            --primary-blue: #004a99;
            --tech-cyan: #00d4ff;
            --glass-bg: rgba(255, 255, 255, 0.12);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            font-family: 'Poppins', sans-serif;
            /* Gradiente Tecnológico Profundo */
            background: radial-gradient(circle at top right, #002f61, #000b1a);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        /* Efecto de Luces de Fondo (Opcional, muy atractivo) */
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

        .login-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            padding: 2.5rem 2rem;
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            width: 95%;
            max-width: 400px;
            color: white;
        }

        .inatec-logo {
            font-weight: 600;
            font-size: 1.8rem;
            text-align: center;
            background: linear-gradient(to right, #fff, var(--tech-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.2rem;
        }

        .sub-title {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            margin-bottom: 2rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Inputs Estilizados */
        .form-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.85rem;
            margin-left: 5px;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            color: white;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--tech-cyan);
            color: white;
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.25);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        /* Botón Pro */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #007bff 100%);
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            margin-top: 1rem;
            box-shadow: 0 10px 20px rgba(0, 74, 153, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(0, 123, 255, 0.4);
            background: linear-gradient(135deg, #0056b3 0%, var(--tech-cyan) 100%);
        }

        /* Personalización del Toggle Password */
        .password-wrapper { position: relative; }
        .toggle-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: rgba(255, 255, 255, 0.5);
            transition: 0.3s;
        }
        .toggle-icon.active-blue { color: var(--tech-cyan) !important; }

        .footer-text {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.4);
        }

        .login-error-alert {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff99a2;
            padding: 10px;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }
        
        /* Ajuste para opciones del select (fondo oscuro) */
        option { background: #001a33; color: white; }
    </style>
</head>

<body>
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
                    <label class="form-check-label small text-white-50" for="recordar">Recordarme</label>
                </div>
                <a href="recordarme.php" class="small text-decoration-none" style="color: var(--tech-cyan);">¿Qué es esto?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100">Entrar al Sistema</button>

            <div class="text-center mt-3">
                <span class="small text-white-50">¿Nuevo aquí?</span>
                <a href="crear-cuenta.html" class="text-decoration-none small fw-bold ms-1" style="color: var(--tech-cyan);">Crear Cuenta</a>
            </div>
        </form>

        <div class="footer-text">© 2026 Nicaragua - Educación Técnica</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="viewpassword.js"></script>
</body>
</html>
