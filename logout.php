<?php
session_start();
require_once "config/db.php";

// Borrar token en BD
if(isset($_SESSION["id"])){
    $sql = "UPDATE usuarios SET remember_token = NULL WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([":id" => $_SESSION["id"]]);
}

// Eliminar cookie
setcookie("remember_token", "", time() - 3600, "/");

session_destroy();

header("Location: index.php");
exit;