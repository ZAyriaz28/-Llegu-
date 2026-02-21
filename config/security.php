<?php
/**
 * Valida si la IP del cliente pertenece a la red autorizada del INATEC
 *
 */
function esRedInatec() {
    // Lista de IPs públicas autorizadas (Ejemplos)
    $ips_autorizadas = [
        '10.253.46.54', // IP pública del centro Somoto
        //'190.92.XXX.XXX'  // Otra posible IP de salida
    ];

    // Obtener la IP real del usuario
    $ip_cliente = $_SERVER['REMOTE_ADDR'];

    // En algunos servidores con proxy se usa:
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_cliente = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    return in_array($ip_cliente, $ips_autorizadas);
}
?>
