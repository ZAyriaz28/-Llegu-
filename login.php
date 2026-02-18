<?php
session_start();

/* ========= CONEXIÓN ========= */
require_once "config/db.php";


/* ========= DATOS FORM ========= */

$usuario = $_POST["user"] ?? "";
$clave   = $_POST["pass"] ?? "";


/* ========= VALIDAR ========= */

if(empty($usuario) || empty($clave)){
    die("Complete todos los campos");
}


/* ========= CONSULTA ========= */

$sql = "
SELECT 
    u.id,
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


/* ========= SESIÓN ========= */

$_SESSION["id"]      = $userData["id"];
$_SESSION["usuario"] = $userData["usuario"];
$_SESSION["rol"]     = $userData["rol"];
$_SESSION["nombre"]  = $userData["nombre"];


/* ========= REDIRECCIÓN ========= */

switch($userData["rol"]){

    case "admin":
        header("Location: admin.php");
        break;

    case "maestro":
        header("Location: dashboard.php");
        break;

    case "estudiante":
        header("Location: estudiante.html");
        break;

    default:
        header("Location: panel.php");
}

exit;
