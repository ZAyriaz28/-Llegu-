<?php
session_start();
require_once "config/db.php";

$usuario = $_POST["user"] ?? "";
$clave   = $_POST["pass"] ?? "";
$rolForm = $_POST["rol"] ?? "";
$recordar = isset($_POST["recordar"]); // Detectamos si marcó el checkbox

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

/* ========= LOGIN EXITOSO ========= */
$_SESSION["id"]      = $userData["id"];
$_SESSION["usuario"] = $userData["usuario"];
$_SESSION["rol"]     = $userData["rol"];
$_SESSION["nombre"]  = $userData["nombre"];

/* ========= LÓGICA DE COOKIES (LO QUE FALTABA) ========= */
if ($recordar) {
    // 1. Generamos un token único y seguro
    $token = bin2hex(random_bytes(32));

    // 2. Guardamos el token en la base de datos para este usuario
    $updateSql = "UPDATE usuarios SET remember_token = :token WHERE id = :id";
    $updateStmt = $db->prepare($updateSql);
    $updateStmt->execute([
        ":token" => $token,
        ":id"    => $userData["id"]
    ]);

    // 3. Creamos la cookie en el navegador (Duración: 30 días)
    // El "/" es vital para que funcione en todo el sitio
    setcookie("remember_token", $token, time() + (30 * 24 * 60 * 60), "/");
} else {
    // Si NO marcó recordar, nos aseguramos de borrar cualquier cookie vieja
    if (isset($_COOKIE["remember_token"])) {
        setcookie("remember_token", "", time() - 3600, "/");
    }
}

// Redirección según el rol
switch($userData["rol"]){
    case "admin": header("Location: admin.php"); break;
    case "maestro": header("Location: dashboard.php"); break;
    case "estudiante": header("Location: estudiante.php"); break;
    default: header("Location: panel.php");
}
exit;
