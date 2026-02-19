<?php
require_once "config/auth.php";
require_once "config/db.php";

if (!isset($_SESSION["id"])) {
    http_response_code(401);
    exit("No autorizado");
}

$usuario_id = $_SESSION["id"];
$clase = $_POST["clase"] ?? null;
$fecha = date("Y-m-d");

if (!$clase) {
    exit("Clase inválida");
}

// Verificar duplicado
$sql = "SELECT id FROM asistencias 
        WHERE usuario_id = :id 
        AND clase = :clase 
        AND fecha = :fecha";

$stmt = $db->prepare($sql);
$stmt->execute([
    ":id" => $usuario_id,
    ":clase" => $clase,
    ":fecha" => $fecha
]);

if ($stmt->fetch()) {
    exit("Ya registraste asistencia hoy");
}

// Insertar
$sqlInsert = "INSERT INTO asistencias (usuario_id, clase, fecha, registrado_en)
              VALUES (:id, :clase, :fecha, NOW())";

$stmtInsert = $db->prepare($sqlInsert);
$stmtInsert->execute([
    ":id" => $usuario_id,
    ":clase" => $clase,
    ":fecha" => $fecha
]);

echo "Asistencia registrada correctamente";