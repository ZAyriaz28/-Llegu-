<?php
// Forzar la hora de Nicaragua para que coincida con tu red
date_default_timezone_set('America/Managua');

require_once "config/auth.php";
require_once "config/db.php";
require_once "config/security.php";

header('Content-Type: application/json');

// 1. Validación de Red WiFi
if (!esRedInatec()) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Fuera de la red autorizada."]);
    exit();
}

// 2. Validar sesión
if (!isset($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Sesión expirada"]);
    exit();
}

$usuario_id = (int) $_SESSION["id"];

// MODIFICACIÓN: Si no viene clase, ponemos un nombre genérico para que la DB no falle
$clase = trim($_POST["clase"] ?? "Ciberseguridad"); 
$fecha = date("Y-m-d");

// 3. Verificar si ya marcó hoy
$sql = "SELECT id FROM asistencias WHERE usuario_id = :id AND fecha = :fecha LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->execute([":id" => $usuario_id, ":fecha" => $fecha]);

if ($stmt->fetch()) {
    echo json_encode(["status" => "existe", "message" => "Ya registraste tu asistencia hoy."]);
    exit();
}

// 4. Insertar (Ahora siempre tendrá un valor en 'clase')
$sqlInsert = "INSERT INTO asistencias (usuario_id, clase, fecha, registrado_en) 
              VALUES (:id, :clase, :fecha, NOW())";
$stmtInsert = $db->prepare($sqlInsert);
$stmtInsert->execute([
    ":id"    => $usuario_id,
    ":clase" => $clase,
    ":fecha" => $fecha
]);

echo json_encode(["status" => "ok", "message" => "Asistencia registrada correctamente"]);
