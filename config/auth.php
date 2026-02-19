<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/db.php";

/* ========= SI NO HAY SESIÓN ========= */

if (!isset($_SESSION["id"])) {

    if (isset($_COOKIE["remember_token"])) {

        $sql = "SELECT u.id, u.usuario, u.nombre, r.nombre AS rol
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE u.remember_token = :token
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ":token" => $_COOKIE["remember_token"]
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {

            $_SESSION["id"]      = $user["id"];
            $_SESSION["usuario"] = $user["usuario"];
            $_SESSION["rol"]     = $user["rol"];
            $_SESSION["nombre"]  = $user["nombre"];

        } else {
            setcookie("remember_token", "", time() - 3600, "/");
        }
    }
}
