<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["pendiente_verificacion"])) {
    http_response_code(403);
    exit("No autorizado");
}

$user_id = $_SESSION["pendiente_verificacion"];

/* Buscar usuario */
$sql = "SELECT nombre, correo FROM usuarios WHERE id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    exit("Usuario no encontrado");
}

$nombre = $user["nombre"];
$correo = $user["correo"];

/* Generar código */
$codigo = random_int(100000,999999);
$expira = date("Y-m-d H:i:s", time()+300);

/* Limpiar */
$db->prepare("DELETE FROM codigos_verificacion WHERE usuario_id=?")
   ->execute([$user_id]);

/* Guardar */
$db->prepare("
INSERT INTO codigos_verificacion (usuario_id,codigo,expira_en)
VALUES (?,?,?)
")->execute([$user_id,$codigo,$expira]);

/* Guardar en sesión */
$_SESSION["email_nombre"] = $nombre;
$_SESSION["email_correo"] = $correo;
$_SESSION["email_codigo"] = $codigo;

echo "OK";