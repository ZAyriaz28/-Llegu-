<?php

session_start();
require_once "config/db.php";

/* ================= VALIDAR SESIÓN ================= */

if (!isset($_SESSION["pendiente_verificacion"])) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION["pendiente_verificacion"];


/* ================= BUSCAR USUARIO ================= */

$sql = "SELECT nombre, correo FROM usuarios WHERE id = ? LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado");
}

$nombre = $usuario["nombre"];
$correo = $usuario["correo"];


/* ================= GENERAR CÓDIGO ================= */

$codigo = random_int(100000, 999999);
$expira = date("Y-m-d H:i:s", time() + 300); // 5 minutos


/* ================= BORRAR CÓDIGOS ANTERIORES ================= */

$sql = "DELETE FROM codigos_verificacion WHERE usuario_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);


/* ================= GUARDAR NUEVO CÓDIGO ================= */

$sql = "INSERT INTO codigos_verificacion (usuario_id, codigo, expira_en)
        VALUES (?,?,?)";

$stmt = $db->prepare($sql);

$stmt->execute([
    $user_id,
    $codigo,
    $expira
]);


/* ================= CONFIG EMAILJS ================= */

$service_id  = "service_z2iq85g";
$template_id = "template_um7o5c8";
$private_key = "aYQj8l4hubsf4dk3f"; // CAMBIAR LUEGO

$url = "https://api.emailjs.com/api/v1.0/email/send";


/* ================= DATOS A ENVIAR ================= */

$data = [

    "service_id"  => $service_id,
    "template_id" => $template_id,
    "user_id"     => $private_key,

    "template_params" => [

        "to_email" => $correo,
        "to_name"  => $nombre,
        "code"     => $codigo

    ]
];

$payload = json_encode($data);


/* ================= ENVIAR A EMAILJS ================= */

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

$response = curl_exec($ch);

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);


/* ================= VERIFICAR RESPUESTA ================= */

if ($http_code !== 200) {

    echo "<h3>Error EmailJS</h3>";
    echo "HTTP: " . $http_code . "<br>";
    echo "Respuesta:<br>";
    echo "<pre>";
    print_r($response);
    echo "</pre>";

    exit;
}


/* ================= REDIRIGIR ================= */

header("Location: esperar_codigo.php?ok=1");
exit;