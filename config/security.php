<?php
if (!function_exists('esRedInatec')) {
    function esRedInatec() {
        // IPs autorizadas del centro
        $ips_autorizadas = [
            '10.253.46.54', // IP de la captura o la de tu centro
            '127.0.0.1', 
            '::1'
        ];

        $ip_cliente = $_SERVER['REMOTE_ADDR'];

        // Manejo de proxy para Render
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip_cliente = trim($ips[0]);
        }

        return in_array($ip_cliente, $ips_autorizadas);
    }
}
