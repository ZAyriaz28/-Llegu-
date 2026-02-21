<?php
function esRedInatec() {
    // IPs autorizadas (Asegúrate de que esta sea la IP PÚBLICA de tu centro)
    $ips_autorizadas = [
        '10.253.46.54', 
        '127.0.0.1', // Para tus pruebas locales
        '::1'        // Para pruebas locales en IPv6
    ];

    $ip_cliente = $_SERVER['REMOTE_ADDR'];

    // Priorizar la IP real si viene a través del proxy de Render
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // A veces vienen varias IPs separadas por coma, agarramos la primera
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip_cliente = trim($ips[0]);
    }

    return in_array($ip_cliente, $ips_autorizadas);
}
