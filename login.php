<?php
session_start();

/* ========= CONEXIÓN ========= */
require_once "config/db.php";

/* ========= DATOS FORM ========= */

$usuario = $_POST["user"] ?? "";
$clave   = $_POST["pass"] ?? "";
$rolForm = $_POST["rol"] ?? "";

/* ========= VALIDAR ========= */

if(empty($usuario) || empty($clave) || empty($rolForm)){
    die("Complete todos los campos");
}

/* ========= CONSULTA ========= */

$sql = "
SELECT 
    u.id,
    u.nombre, 
    u.usuario,
    u.password,
    r.nombre AS rol
FROM usuarios u
INNER JOIN roles r ON u.rol_id = r.id
WHERE (u.usuario = :u OR u.correo = :u)
AND u.verified = 1
LIMIT 1
";

$stmt = $db->prepare($sql);

$stmt->execute([
    ":u" => $usuario
]);

$userData = $stmt->fetch(PDO::FETCH_ASSOC);

/* ========= EXISTE ========= */

if(!$userData){
    die("Usuario no encontrado o no verificado");
}

/* ========= PASSWORD ========= */

if(!password_verify($clave, $userData["password"])){
    die("Contraseña incorrecta");
}

/* ========= VALIDAR ROL ========= */

if($rolForm !== $userData["rol"]){
    die("Tu cuenta no pertenece al rol seleccionado");
}

/* ========= SESIÓN ========= */

$_SESSION["id"]      = $userData["id"];
$_SESSION["usuario"] = $userData["usuario"];
$_SESSION["rol"]     = $userData["rol"];
$_SESSION["nombre"]  = $userData["nombre"];

/* ========= COOKIE RECORDAR ========= */

if(isset($_POST["recordar"])){

    // Generar token seguro
    $token = bin2hex(random_bytes(32));

    // Guardar token en BD
    $sqlToken = "UPDATE usuarios SET remember_token = :token WHERE id = :id";
    $stmtToken = $db->prepare($sqlToken);
    $stmtToken->execute([
        ":token" => $token,
        ":id" => $userData["id"]
    ]);

    // Crear cookie válida por 30 días
    setcookie(
        "remember_token",
        $token,
        time() + (86400 * 30), // 30 días
        "/",
        "",
        false,
        true // HttpOnly
    );
}

/* ========= REDIRECCIÓN ========= */

switch($userData["rol"]){

    case "admin":
        header("Location: admin.php");
        break;

    case "maestro":
        header("Location: dashboard.php");
        break;

    case "estudiante":
        header("Location: estudiante.php");
        break;

    default:
        header("Location: panel.php");
}

exit;