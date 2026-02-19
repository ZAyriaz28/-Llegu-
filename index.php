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
            margin-bottom: 2rem;
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

        .btn-registro {
            background: linear-gradient(145deg, #ffffff, #e3e9ad,rgb(152, 174, 247));
            border: 1px solid #d2d2b4;
            text-align: center;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1),
                inset -2px -2px 4px rgba(0, 0, 0, 0.05);
        }

        .btn-registro:hover {
            background: #F5F5DC;
            box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.15);
        }
    </style>

</head><body><div class="login-container"><div class="inatec-logo">

    Sistema Educativo

</div>



<div class="sub-title">

    Tecnológico Nacional - Nicaragua

</div>





<!-- FORMULARIO -->

<form action="login.php" method="POST">



    <!-- Usuario -->

    <div class="mb-3">

        <label for="user" class="form-label text-dark fw-medium">

            Usuario o Correo

        </label>



        <input type="text"

               class="form-control"

               id="user"

               name="user"

               placeholder="nombre.apellido"

               required>

    </div>





    <!-- Contraseña -->

    <div class="mb-3">

        <label for="pass" class="form-label text-dark fw-medium">

            Contraseña

        </label>



        <input type="password"

               class="form-control"

               id="pass"

               name="pass"

               placeholder="••••••••"

               required>

    </div>





    <!-- Rol -->

    <div class="mb-4">

        <label class="form-label text-dark fw-medium">

            Tipo de Usuario

        </label>



        <select class="form-select" name="rol" required>

            <option value="estudiante">Estudiante</option>

            <option value="maestro">Docente / Maestro</option>

            <option value="admin">Administrador</option>

        </select>

    </div>

 <!-- RECORDARME -->
    <div class="mb-3 d-flex align-items-center justify-content-between">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="recordar" name="recordar" value="1">
            <label class="form-check-label fw-medium text-dark" for="recordar">
                Recordarme
            </label>
        </div>
        <a href="recordarme.php" class="small text-decoration-none fw-semibold text-primary">
            ¿Qué es esto?
        </a>
    </div>

 <!-- Botón --> 

    <button type="submit" class="btn btn-primary w-100 mb-3">
        Entrar al Sistema
    </button>


     <div class="text-center">
            <span class="small text-muted">¿No Tienes Cuenta?</span>
            <a href="crear-cuenta.html" class="text-decoration-none small fw-bold text-primary ms-1">
                Crear Cuenta
            </a>
    </div>



</form>





<div class="footer-text">

    © 2026 Nicaragua - Educación Técnica

</div>

</div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script></body></html>
