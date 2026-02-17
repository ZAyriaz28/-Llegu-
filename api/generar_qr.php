<?php
session_start();
require_once "../config/db.php";

header("Content-Type: application/json");

/* Seguridad */
if(!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "maestro"){
    http_response_code(403);
    echo json_encode(["error"=>"No autorizado"]);
    exit;
}

/* Datos */
$clase = "Tecnico-A1";
$fecha = date("Y-m-d");

/* Token seguro */
$token = bin2hex(random_bytes(20));

/* Guardar sesión */

$stmt = $db->prepare("
    INSERT INTO sesiones_qr
    (clase,fecha,token,creado_en)
    VALUES (?,?,?,NOW())
");

$stmt->execute([
    $clase,
    $fecha,
    $token
]);

echo json_encode([
    "token"=>$token,
    "clase"=>$clase,
    "fecha"=>$fecha
]);