<?php

session_start();
require_once "config/db.php";


/* ================= DATOS ================= */

$nombre = trim($_POST["nombre"] ?? "");
$correo = trim($_POST["correo"] ?? "");
$user   = trim($_POST["user"] ?? "");
$tipo   = trim($_POST["tipo"] ?? "");
$pass   = $_POST["pass"] ?? "";
$pass2  = $_POST["pass_confirm"] ?? "";


/* ================= VALIDAR ================= */

if(
    !$nombre || !$correo || !$user || !$pass || !$pass2
){
    die("Campos incompletos");
}

if($pass !== $pass2){
    die("Las contraseñas no coinciden");
}


/* ================= DUPLICADOS ================= */

$sql = "SELECT id FROM usuarios WHERE usuario = ? OR correo = ? LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->execute([$user,$correo]);

if($stmt->fetch()){
    die("Usuario o correo ya registrado");
}


/* ================= HASH ================= */

$hash = password_hash($pass, PASSWORD_DEFAULT);


/* ================= ROL ================= */

$rol_id = ($tipo==="maestro") ? 2 : 3;


/* ================= INSERT ================= */

$sql = "INSERT INTO usuarios
(nombre,usuario,correo,password,rol_id,verified)
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


/* ================= SESION ================= */

$_SESSION["pendiente_verificacion"] = $user_id;


/* ================= REDIRIGIR ================= */

header("Location: esperar_codigo.php");
exit;