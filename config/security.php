<?php
if (!function_exists('esRedInatec')) {
    function esRedInatec() {
        // Lista de IPs autorizadas
        $ips_autorizadas = [
            '165.98.243.55',    // IP Pública real del INATEC (¡Esta es la clave!)
            '10.253.46.54',     // Tu IP de red local
            '127.0.0.1', 
            '::1'               // Para pruebas locales
        ];

        // Intentar obtener la IP real del usuario a través del proxy de Render
        $ip_cliente = $_SERVER['REMOTE_ADDR'];

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Render pasa la IP original en esta cabecera
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip_cliente = trim($ips[0]);
        }

        return in_array($ip_cliente, $ips_autorizadas);
    }
}
