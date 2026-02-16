<?php
session_start();
require_once "config/db.php";

/* Verificar conexión */
if (!isset($pdo)) {
    die("❌ Error: No hay conexión a la base de datos");
}

/* Validar sesión */
if (!isset($_SESSION["pendiente_verificacion"])) {
    header("Location: index.html");
    exit;
}

$email  = $_SESSION["pendiente_verificacion"];
$codigo = $_POST["codigo"] ?? "";

/* Validar datos */
if (empty($codigo)) {
    die("❌ Debes ingresar el código");
}

/* Buscar código */

$sql = $pdo->prepare("
    SELECT id 
    FROM codigos_verificacion
    WHERE email = ? AND codigo = ?
    ORDER BY id DESC
    LIMIT 1
");

$sql->execute([$email, $codigo]);

if ($sql->rowCount() == 0) {
    die("❌ Código incorrecto");
}

/* Activar usuario */

$up = $pdo->prepare("
    UPDATE usuarios 
    SET verificado = 1
    WHERE email = ?
");

$up->execute([$email]);

/* Borrar códigos usados */

$del = $pdo->prepare("
    DELETE FROM codigos_verificacion
    WHERE email = ?
");

$del->execute([$email]);

/* Limpiar sesión */

unset($_SESSION["pendiente_verificacion"]);

echo "✅ Cuenta verificada correctamente";