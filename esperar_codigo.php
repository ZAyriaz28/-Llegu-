<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["pendiente_verificacion"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["pendiente_verificacion"];

// Buscar usuario
$sql = "SELECT nombre, correo FROM usuarios WHERE id = ? LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Usuario no encontrado");
}

$nombre = $user["nombre"];
$correo = $user["correo"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Seguridad - INATEC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.min.js"></script>
    <script> (function(){ emailjs.init("aYQj8l4hubsf4dk3f"); })(); </script>

    <style>
        :root {
            --primary-blue: #004a99;
            --tech-cyan: #00d4ff;
            --glass-bg: rgba(255, 255, 255, 0.12);
            --glass-border: rgba(255, 255, 255, 0.2);
            --success-green: #28a745;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at center, #002f61, #000b1a);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: white;
            overflow: hidden;
        }

        .box {
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            padding: 3rem 2rem;
            border-radius: 30px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            width: 95%;
            max-width: 420px;
            text-align: center;
        }

        .icon-box {
            font-size: 3rem;
            color: var(--tech-cyan);
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 0 10px rgba(0, 212, 255, 0.5));
        }

        h2 {
            font-weight: 600;
            background: linear-gradient(to right, #fff, var(--tech-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        .info-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .email-highlight {
            color: var(--tech-cyan);
            font-weight: 500;
            display: block;
            margin-top: 5px;
        }

        /* Input de Código Estilo 'Elegante' */
        .code-input {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 15px;
            font-size: 2rem;
            letter-spacing: 12px;
            text-align: center;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .code-input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--tech-cyan);
            outline: none;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
            transform: scale(1.02);
        }

        /* Botones */
        .btn-verify {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #007bff 100%);
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: 0.3s;
            box-shadow: 0 8px 20px rgba(0, 74, 153, 0.3);
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(0, 212, 255, 0.3);
        }

        .btn-resend {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.6);
            margin-top: 1rem;
            font-size: 0.85rem;
            padding: 8px 20px;
            border-radius: 10px;
            transition: 0.3s;
        }

        .btn-resend:hover {
            border-color: var(--tech-cyan);
            color: var(--tech-cyan);
            background: rgba(0, 212, 255, 0.05);
        }

        .alert-sent {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #98ffb3;
            padding: 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>

<div class="box animate__animated animate__fadeInDown">
    
    <div class="icon-box">
        <i class="bi bi-shield-lock-fill"></i>
    </div>

    <?php if(isset($_GET["ok"])): ?>
        <div class="alert-sent animate__animated animate__pulse">
            <i class="bi bi-check-circle-fill me-2"></i> Código enviado con éxito
        </div>
    <?php endif; ?>

    <h2>Verificación</h2>
    <p class="info-text">
        Hemos enviado un código de seguridad a:
        <span class="email-highlight"><?php echo $correo; ?></span>
    </p>

    <form action="verificar_codigo.php" method="POST">
        <input type="text" 
               name="codigo" 
               class="code-input"
               maxlength="6" 
               required 
               placeholder="000000"
               autocomplete="off">

        <button type="submit" class="btn-verify">
            Validar Identidad
        </button>
    </form>

    <button onclick="reenviarCodigo()" class="btn-resend">
        <i class="bi bi-arrow-clockwise me-1"></i> Reenviar código
    </button>

    <div class="mt-4">
        <a href="logout.php" class="text-white-50 small text-decoration-none">Cancelar proceso</a>
    </div>
</div>

<script>
function reenviarCodigo(){
    const btn = document.querySelector('.btn-resend');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
    btn.disabled = true;

    fetch("enviar_codigo.php")
    .then(res => res.text())
    .then(codigo => {
        if(codigo === "NO_SESSION" || codigo === "NO_USER"){
            alert("Sesión inválida");
            location.reload();
            return;
        }

        const params = {
            user_name: <?php echo json_encode($nombre); ?>,
            to_email: <?php echo json_encode($correo); ?>,
            verification_code: codigo
        };

        emailjs.send("service_z2iq85g", "template_um7o5c8", params)
        .then(() => {
            window.location = "esperar_codigo.php?ok=1";
        })
        .catch(error => {
            alert("Error EmailJS: " + error.text);
            btn.disabled = false;
            btn.innerHTML = 'Reenviar código';
        });
    })
    .catch(err => {
        alert("Error servidor");
        btn.disabled = false;
    });
}
</script>

</body>
</html>
