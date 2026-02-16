<?php
session_start();
require "config/db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "PHPMailer/Exception.php";
require "PHPMailer/PHPMailer.php";
require "PHPMailer/SMTP.php";

if(!isset($_SESSION["pendiente_verificacion"])){
    header("Location: index.html");
    exit;
}

$email = $_SESSION["pendiente_verificacion"];

$codigo = rand(100000,999999);

/* Guardar código */

$stmt = $pdo->prepare("
    INSERT INTO codigos_verificacion(email,codigo)
    VALUES(?,?)
");

$stmt->execute([$email,$codigo]);

/* Enviar mail */

$mail = new PHPMailer(true);

try{

    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;

    $mail->Username = "TU_CORREO@gmail.com";
    $mail->Password = "TU_CLAVE_APP";

    $mail->SMTPSecure = "tls";
    $mail->Port = 587;

    $mail->setFrom("TU_CORREO@gmail.com","Sistema");
    $mail->addAddress($email);

    $mail->isHTML(true);

    $mail->Subject = "Codigo de verificacion";

    $mail->Body = "
        <h2>Tu código es:</h2>
        <h1>$codigo</h1>
    ";

    $mail->send();

}catch(Exception $e){

    die("Error correo: ".$mail->ErrorInfo);

}

header("Location: esperar_codigo.php");
exit;