<?php
session_start();

/* ========= CONFIG BD ========= */

$host = "yallegue-luishebertosuarezflores-2522.f.aivencloud.com";
$dbname = "defaultdb";
$user = "avnadmin";
$pass = "AVNS_g1CmAIgcRPKaMmAkN_I";
$port = 20421;


/* ========= CONEXIÓN ========= */

try {

    $db = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
        $user,
        $pass
    );

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e){

    die("Error conexión: " . $e->getMessage());

}


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
WHERE u.usuario = :u OR u.correo = :u
LIMIT 1
";

$stmt = $db->prepare($sql);
$stmt->execute([
    ":u" => $usuario
]);

$userData = $stmt->fetch(PDO::FETCH_ASSOC);


/* ========= EXISTE ========= */

if(!$userData){

    die("Usuario no encontrado");

}


/* ========= PASSWORD ========= */

if(!password_verify($clave, $userData["password"])){

    die("Contraseña incorrecta");

}


/* ========= SESIÓN ========= */

$_SESSION["id"] = $userData["id"];
$_SESSION["usuario"] = $userData["usuario"];
$_SESSION["rol"] = $userData["rol"];


/* ========= REDIRECCIÓN ========= */

switch($userData["rol"]){

    case "admin":
        header("Location: admin.php");
        break;

    case "maestro":
        header("Location: maestro.html");
        break;

    case "estudiante":
        header("Location: estudiante.html");
        break;

    default:
        header("Location: panel.php");
}

exit;

?>
