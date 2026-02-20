<?php

$host = "yallegue-luishebertosuarezflores-2522.f.aivencloud.com";
$dbname = "defaultdb";
$user = "avnadmin";
$pass = "AVNS_g1CmAIgcRPKaMmAkN_I";
$port = 20421;

try {

    $db = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
        $user,
        $pass
    );

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e){

    die("Error BD: " . $e->getMessage());

}



//=============================================================================================================================================================

 // Valida si la IP del cliente pertenece a la red autorizada del INATEC
function esRedInatec() {
    // Lista de IPs públicas autorizadas (Ejemplos)
    $ips_autorizadas = [
        '186.77.XXX.XXX', // IP pública del centro Somoto
        '190.92.XXX.XXX'  // Otra posible IP de salida
    ];

    // Obtener la IP real del usuario
    $ip_cliente = $_SERVER['REMOTE_ADDR'];

    // En algunos servidores con proxy se usa:
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_cliente = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    return in_array($ip_cliente, $ips_autorizadas);
}
