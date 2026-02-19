<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información - Recordarme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{
            background: linear-gradient(135deg, #004a99 0%, #007bff 100%);
            height: 100vh;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .card-info{
            max-width:500px;
            border-radius:20px;
            padding:2rem;
            box-shadow:0 15px 35px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

<div class="card bg-white card-info">
    <h4 class="text-primary mb-3">¿Qué hace la opción "Recordarme"?</h4>

    <p class="text-muted">
        Al activar esta opción, el sistema guardará una cookie segura en tu navegador.
        Esto permitirá que tu sesión se mantenga iniciada automáticamente durante 30 días,
        incluso si cierras el navegador.
    </p>

    <p class="text-muted">
        Recomendamos usar esta opción solo en dispositivos personales y seguros.
    </p>

    <a href="index.php" class="btn btn-primary w-100 mt-3">
        Volver al Inicio
    </a>
</div>

</body>
</html>