<?php
session_start();
require_once "config/db.php";

/* PHPMailer con Composer */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/vendor/autoload.php";

/* Verificar sesión */

if (!isset($_SESSION["pendiente_verificacion"])) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION["pendiente_verificacion"];


/* Buscar datos del usuario */

$sql = "SELECT nombre, correo FROM usuarios WHERE id = ? LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado");
}

$nombre = $usuario["nombre"];
$correo = $usuario["correo"];


/* Generar código */

$codigo = random_int(100000, 999999);
$expira = date("Y-m-d H:i:s", time() + 300); // 5 min


/* Borrar códigos anteriores */

$sql = "DELETE FROM codigos_verificacion WHERE usuario_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);


/* Guardar nuevo código */

$sql = "INSERT INTO codigos_verificacion (usuario_id, codigo, expira_en)
        VALUES (?,?,?)";

$stmt = $db->prepare($sql);

$stmt->execute([
    $user_id,
    $codigo,
    $expira
]);


/* Enviar correo */

$mail = new PHPMailer(true);

try {

    // Config SMTP
    $mail->isSMTP();
    $mail->Host       = "smtp.gmail.com";
    $mail->SMTPAuth   = true;

    $mail->Username   = "correo.automatizado.yallegue@gmail.com";
    $mail->Password   = "qmoe cvih uewo idfh";

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Remitente
    $mail->setFrom("correo.automatizado.yallegue@gmail.com", "Sistema Escolar");

    // Destino
    $mail->addAddress($correo, $nombre);

    // Contenido
    $mail->isHTML(true);

    $mail->Subject = "Código de Verificación";

    $mail->Body = "
        <div style='font-family:Arial'>
            <h2>Verificación</h2>

            <p>Hola <b>$nombre</b>,</p>

            <p>Tu código es:</p>

            <h1 style='color:#ff6600'>$codigo</h1>

            <p>Vence en 5 minutos.</p>
        </div>
    ";

    $mail->AltBody = "Tu código es: $codigo";

    $mail->send();

} catch (Exception $e) {

    die("Error enviando correo: " . $mail->ErrorInfo);

}


/* Volver a pantalla */

header("Location: esperar_codigo.php");
exit;