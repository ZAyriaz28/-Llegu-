<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require_once "config/db.php";
require __DIR__ . '/vendor/autoload.php';

/* ================= DATOS ================= */

$nombre = trim($_POST["nombre"] ?? "");
$correo = trim($_POST["correo"] ?? "");
$user   = trim($_POST["user"] ?? "");
$tipo   = trim($_POST["tipo"] ?? "");
$pass   = $_POST["pass"] ?? "";
$pass2  = $_POST["pass_confirm"] ?? "";

/* ================= VALIDAR ================= */

if (
    empty($nombre) ||
    empty($correo) ||
    empty($user) ||
    empty($pass) ||
    empty($pass2)
) {
    die("Campos incompletos");
}

if ($pass !== $pass2) {
    die("Las contraseñas no coinciden");
}

/* ================= VALIDAR DUPLICADOS ================= */

$sql = "SELECT id FROM usuarios WHERE usuario = ? OR correo = ? LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->execute([$user, $correo]);

if ($stmt->fetch()) {
    die("Usuario o correo ya registrado");
}

/* ================= HASH ================= */

$hash = password_hash($pass, PASSWORD_DEFAULT);

/* ================= ROL ================= */

$rol_id = ($tipo === "maestro") ? 2 : 3;

/* ================= INSERTAR USUARIO ================= */

$sql = "INSERT INTO usuarios 
(nombre, usuario, correo, password, rol_id, verified)
VALUES (?,?,?,?,?,0)";

$stmt = $db->prepare($sql);

$stmt->execute([
    $nombre,
    $user,
    $correo,
    $hash,
    $rol_id
]);

$user_id = $db->lastInsertId();

/* ================= GENERAR CÓDIGO ================= */

$codigo = random_int(100000, 999999);
$expira = date("Y-m-d H:i:s", time() + 300);

/* ================= GUARDAR CÓDIGO ================= */

$sql = "INSERT INTO codigos_verificacion
(usuario_id, codigo, expira_en)
VALUES (?,?,?)";

$stmt = $db->prepare($sql);

$stmt->execute([
    $user_id,
    $codigo,
    $expira
]);

/* ================= SESIÓN ================= */

$_SESSION["pendiente_verificacion"] = $user_id;


/* ================= MAIL ================= */

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;

    // TU CORREO REAL (sin espacios)
    $mail->Username   = 'correo.automatizado.yallegue@gmail.com';

    // Contraseña de aplicación
    $mail->Password   = 'qmoecvihuewoidfh';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Remitente
    $mail->setFrom('correo.automatizado.yallegue@gmail.com', 'Sistema Escolar');

    // Destinatario
    $mail->addAddress($correo, $nombre);

    // Contenido
    $mail->isHTML(true);
    $mail->Subject = 'Codigo de Verificacion';

    $mail->Body = "
        <div style='font-family:Arial'>
            <h2>Verificación de cuenta</h2>

            <p>Hola <b>$nombre</b>,</p>

            <p>Tu código es:</p>

            <h1 style='color:#ff6600'>$codigo</h1>

            <p>Este código vence en 5 minutos.</p>
        </div>
    ";

    $mail->AltBody = "Tu codigo es: $codigo";

    $mail->send();

} catch (Exception $e) {

    die("Error enviando correo: " . $mail->ErrorInfo);
}


/* ================= REDIRECCIÓN ================= */

header("Location: esperar_codigo.php");
exit;