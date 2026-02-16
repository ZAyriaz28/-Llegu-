<?php
session_start();
require_once "config/db.php";

header("Content-Type: text/plain");

// Validar sesión
if (!isset($_SESSION["pendiente_verificacion"])) {
    http_response_code(401);
    echo "NO_SESSION";
    exit;
}

$user_id = $_SESSION["pendiente_verificacion"];


// Buscar usuario
$sql = "SELECT nombre, correo FROM usuarios WHERE id = ? LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo "NO_USER";
    exit;
}


// Generar código
$codigo = random_int(100000, 999999);
$expira = date("Y-m-d H:i:s", time() + 300);


// Borrar anteriores
$sql = "DELETE FROM codigos_verificacion WHERE usuario_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);


// Guardar nuevo
$sql = "INSERT INTO codigos_verificacion (usuario_id, codigo, expira_en)
        VALUES (?,?,?)";

$stmt = $db->prepare($sql);

$stmt->execute([
    $user_id,
    $codigo,
    $expira
]);


// Devolver código
echo $codigo;
exit;