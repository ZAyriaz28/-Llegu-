<?php
// Capturamos el código de error desde la URL (enviado por el .htaccess)
$code = $_GET['code'] ?? '404';

// Definimos los mensajes para cada error
$errors = [
    '401' => [
        'title' => 'No Autorizado',
        'desc'  => 'Necesitas iniciar sesión para acceder a este recurso.',
        'icon'  => 'bi-person-lock'
    ],
    '402' => [
        'title' => 'Pago Requerido',
        'desc'  => 'Este recurso requiere un pago o suscripción activa.',
        'icon'  => 'bi-credit-card-2-front'
    ],
    '403' => [
        'title' => 'Acceso Prohibido',
        'desc'  => 'No tienes permisos para ver esta página (Ej. Solo Docentes).',
        'icon'  => 'bi-shield-slash-fill'
    ],
    '404' => [
        'title' => 'Página No Encontrada',
        'desc'  => 'La ruta que buscas no existe o ha sido movida.',
        'icon'  => 'bi-sign-dead-end'
    ],
    '500' => [
        'title' => 'Error del Servidor',
        'desc'  => 'Algo salió mal en nuestros sistemas. Intenta más tarde.',
        'icon'  => 'bi-server'
    ]
];

// Si el código no existe en nuestro array, usamos 404 por defecto
$current = $errors[$code] ?? $errors['404'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?php echo $code; ?> - INATEC</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --primary-blue: #004a99;
            --tech-cyan: #00d4ff;
            --bg-gradient: radial-gradient(circle at center, #002f61, #000b1a);
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #ffffff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-gradient);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            overflow: hidden;
            margin: 0;
        }

        .error-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            padding: 3rem;
            border-radius: 30px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            position: relative;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 700;
            line-height: 1;
            background: linear-gradient(to bottom, #fff, var(--tech-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0;
        }

        .icon-floating {
            font-size: 4rem;
            color: var(--tech-cyan);
            margin-bottom: 1rem;
            filter: drop-shadow(0 0 15px rgba(0, 212, 255, 0.4));
            animation: float 3s ease-in-out infinite;
        }

        h2 { font-weight: 600; margin-bottom: 1rem; }
        p { color: rgba(255,255,255,0.6); margin-bottom: 2rem; }

        .btn-home {
            background: linear-gradient(135deg, var(--primary-blue), #007bff);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(0, 74, 153, 0.4);
            display: inline-block;
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(0, 212, 255, 0.3);
            color: white;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body>

    <div class="error-container animate__animated animate__zoomIn">
        <div class="icon-floating">
            <i class="bi <?php echo $current['icon']; ?>"></i>
        </div>
        
        <h1 class="error-code"><?php echo $code; ?></h1>
        <h2><?php echo $current['title']; ?></h2>
        <p><?php echo $current['desc']; ?></p>

        <a href="index.php" class="btn-home">
            <i class="bi bi-house-door-fill me-2"></i>Regresar al Inicio
        </a>
    </div>

</body>
</html>
