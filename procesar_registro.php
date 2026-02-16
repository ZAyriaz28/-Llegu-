<?php
session_start();
require_once "config/db.php";

/* DATOS */

$nombre = $_POST["nombre"] ?? "";
$correo = $_POST["correo"] ?? "";
$user   = $_POST["user"] ?? "";
$tipo   = $_POST["tipo"] ?? "";
$pass   = $_POST["pass"] ?? "";
$pass2  = $_POST["pass_confirm"] ?? "";

/* VALIDAR */

if(!$nombre || !$correo || !$user || !$pass){
    die("Campos incompletos");
}

if($pass !== $pass2){
    die("Las contraseñas no coinciden");
}

/* HASH */

$hash = password_hash($pass, PASSWORD_DEFAULT);

/* ROL */

$rol_id = ($tipo == "maestro") ? 2 : 3;

/* INSERTAR USUARIO */

$sql = "INSERT INTO usuarios 
(usuario, correo, password, rol_id, verified)
VALUES (?,?,?,?,0)";

$stmt = $db->prepare($sql);

$stmt->execute([
    $user,
    $correo,
    $hash,
    $rol_id
]);

$user_id = $db->lastInsertId();

/* GENERAR CÓDIGO */

$codigo = random_int(100000, 999999);
$expira = date("Y-m-d H:i:s", time()+300);

/* GUARDAR CODIGO */

$sql = "INSERT INTO codigos_verificacion
(usuario_id, codigo, expira_en)
VALUES (?,?,?)";

$stmt = $db->prepare($sql);

$stmt->execute([
    $user_id,
    $codigo,
    $expira
]);

/* GUARDAR EN SESION */

$_SESSION["pendiente_verificacion"] = $user_id;

/* ENVIAR MAIL */
// (aquí va PHPMailer luego)

/* REDIRIGIR */

header("Location: esperar_codigo.php");
exit;