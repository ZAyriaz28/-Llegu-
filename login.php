<?php
session_start();
require_once "config/db.php";

$usuario = $_POST["user"] ?? "";
$clave   = $_POST["pass"] ?? "";
$rolForm = $_POST["rol"] ?? "";

// Función auxiliar para fallar con estilo
function fail($mensaje) {
    $_SESSION["error_login"] = $mensaje;
    header("Location: index.php");
    exit;
}

if(empty($usuario) || empty($clave) || empty($rolForm)){
    fail("Por favor, complete todos los campos.");
}

/* ========= CONSULTA ========= */
$sql = "SELECT u.id, u.nombre, u.usuario, u.password, r.nombre AS rol 
        FROM usuarios u 
        INNER JOIN roles r ON u.rol_id = r.id 
        WHERE (u.usuario = :u OR u.correo = :u) AND u.verified = 1 LIMIT 1";

$stmt = $db->prepare($sql);
$stmt->execute([":u" => $usuario]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$userData){
    fail("El usuario no existe o no ha sido verificado.");
}

if(!password_verify($clave, $userData["password"])){
    fail("La contraseña ingresada es incorrecta.");
}

if($rolForm !== $userData["rol"]){
    fail("Tu cuenta no tiene permisos para entrar como " . ucfirst($rolForm));
}

// ... Si todo está bien, continúa tu código de sesión y cookies ...
$_SESSION["id"]      = $userData["id"];
$_SESSION["usuario"] = $userData["usuario"];
$_SESSION["rol"]     = $userData["rol"];
$_SESSION["nombre"]  = $userData["nombre"];

// Redirección exitosa
switch($userData["rol"]){
    case "admin": header("Location: admin.php"); break;
    case "maestro": header("Location: dashboard.php"); break;
    case "estudiante": header("Location: estudiante.php"); break;
    default: header("Location: panel.php");
}
exit;
