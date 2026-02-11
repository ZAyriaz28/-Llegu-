<?php
session_start();

/* ====== CONFIG ====== */

$host = "yallegue-luishebertosuarezflores-2522.f.aivencloud.com";
$dbname = "defaultdb";
$user = "avnadmin";
$pass = "AVNS_g1CmAIgcRPKaMmAkN_I";
$port = 20421;


/* ====== CONEXIÓN ====== */

try {

    $db = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
        $user,
        $pass
    );

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e){

    die("Error: " . $e->getMessage());

}


/* ====== DATOS ====== */

$usuario = $_POST["user"] ?? "";
$clave   = $_POST["pass"] ?? "";
$tipo    = $_POST["tipo"] ?? "";


/* ====== VALIDAR ====== */

if(!$usuario || !$clave){

    die("Campos vacíos");

}


/* ====== BUSCAR ====== */

$sql = "SELECT * FROM usuarios 
        WHERE usuario = :u OR correo = :u";

$stmt = $db->prepare($sql);

$stmt->execute([
    ":u" => $usuario
]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);


/* ====== EXISTE? ====== */

if(!$data){

    die("Usuario no existe");

}


/* ====== PASSWORD ====== */

if(!password_verify($clave, $data["password"])){

    die("Clave incorrecta");

}


/* ====== ROL ====== */

if($tipo == 1 && empty($data["estudiante"])){

    die("No eres estudiante");

}

if($tipo == 2 && empty($data["maestro"])){

    die("No eres maestro");

}


/* ====== SESIÓN ====== */

$_SESSION["id"] = $data["id"];
$_SESSION["usuario"] = $data["usuario"];
$_SESSION["rol"] = $tipo;


/* ====== OK ====== */

header("Location: panel.php");
exit;

?>
