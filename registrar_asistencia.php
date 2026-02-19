<?php

require_once "config/auth.php";
require_once "config/db.php";

header('Content-Type: application/json');

// Validar sesión
if (!isset($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit();
}

//  Validar rol
if (($_SESSION["rol"] ?? "") !== "estudiante") {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Acceso denegado"]);
    exit();
}

$usuario_id = (int) $_SESSION["id"];
$clase      = trim($_POST["clase"] ?? "");
$fecha      = date("Y-m-d");

if ($clase === "") {
    echo json_encode(["status" => "error", "message" => "Clase inválida"]);
    exit();
}

// Verificar duplicado
$sql = "SELECT id 
        FROM asistencias 
        WHERE usuario_id = :id 
        AND clase = :clase 
        AND fecha = :fecha 
        LIMIT 1";

$stmt = $db->prepare($sql);
$stmt->execute([
    ":id"    => $usuario_id,
    ":clase" => $clase,
    ":fecha" => $fecha
]);

if ($stmt->fetch()) {
    echo json_encode(["status" => "existe"]);
    exit();
}

//  Insertar asistencia
$sqlInsert = "INSERT INTO asistencias 
              (usuario_id, clase, fecha, registrado_en)
              VALUES (:id, :clase, :fecha, NOW())";

$stmtInsert = $db->prepare($sqlInsert);
$stmtInsert->execute([
    ":id"    => $usuario_id,
    ":clase" => $clase,
    ":fecha" => $fecha
]);

echo json_encode(["status" => "ok"]);