<?php
session_start();
require_once "config/db.php";

/* Debe estar logueado */
if(!isset($_SESSION["usuario_id"])){
    die("Debes iniciar sesión");
}

/* Token */
if(!isset($_GET["token"])){
    die("QR inválido");
}

$token = $_GET["token"];
$usuario = $_SESSION["usuario_id"];

/* Buscar sesión válida */

$stmt = $db->prepare("
    SELECT * FROM sesiones_qr
    WHERE token = ?
    AND creado_en >= NOW() - INTERVAL 10 MINUTE
");

$stmt->execute([$token]);

$sesion = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$sesion){
    die("QR vencido");
}

/* Verificar duplicado */

$check = $db->prepare("
    SELECT id FROM asistencias
    WHERE usuario_id=? AND fecha=? AND clase=?
");

$check->execute([
    $usuario,
    $sesion["fecha"],
    $sesion["clase"]
]);

if($check->rowCount()>0){
    die("Ya registraste asistencia");
}

/* Registrar */

$ins = $db->prepare("
    INSERT INTO asistencias
    (usuario_id,clase,fecha,registrado_en)
    VALUES (?,?,?,NOW())
");

$ins->execute([
    $usuario,
    $sesion["clase"],
    $sesion["fecha"]
]);

echo "✅ Asistencia registrada";