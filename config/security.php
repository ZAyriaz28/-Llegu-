<?php
if (!function_exists('esRedInatec')) {
    function esRedInatec() {
        // Lista de IPs autorizadas (Puedes añadir todas las que necesites aquí)
        $ips_autorizadas = [
            '165.98.243.55',    // IP Pública INATEC
            '190.212.210.136',   // <-- NUEVA IP AUTORIZADA (Añadida aquí)
            '10.253.46.54',     // Red Local
            '127.0.0.1',        // Localhost IPv4
            '::1'               // Localhost IPv6
        ];

        // Obtener la IP real tras el proxy de Render
        $ip_cliente = $_SERVER['REMOTE_ADDR'];

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Extraer la primera IP de la lista (la del cliente original)
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip_cliente = trim($ips[0]);
        }

        // Comprobar si la IP del cliente está en nuestra lista permitida
        return in_array($ip_cliente, $ips_autorizadas);
    }
}
