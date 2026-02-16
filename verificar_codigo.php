<?php
session_start();
require_once "config/db.php";

/* Verificar conexión */
if (!isset($db)) {
    die("❌ Error: No hay conexión a la base de datos");
}

/* Validar sesión */
if (!isset($_SESSION["pendiente_verificacion"])) {
    header("Location: index.html");
    exit;
}

/* Datos */
$user_id = $_SESSION["pendiente_verificacion"];
$codigo  = $_POST["codigo"] ?? "";

/* Validar código */
if (empty($codigo)) {
    die("❌ Debes ingresar el código");
}


/* ================= BUSCAR CÓDIGO ================= */

$sql = $db->prepare("
    SELECT id
    FROM codigos_verificacion
    WHERE usuario_id = ? AND codigo = ?
    AND expira_en > NOW()
    ORDER BY id DESC
    LIMIT 1
");

$sql->execute([$user_id, $codigo]);

if ($sql->rowCount() == 0) {
    die("❌ Código incorrecto o vencido");
}


/* ================= ACTIVAR USUARIO ================= */

$up = $db->prepare("
    UPDATE usuarios
    SET verified = 1
    WHERE id = ?
");

$up->execute([$user_id]);


/* ================= BORRAR CÓDIGOS ================= */

$del = $db->prepare("
    DELETE FROM codigos_verificacion
    WHERE usuario_id = ?
");

$del->execute([$user_id]);


/* ================= LIMPIAR SESIÓN ================= */

unset($_SESSION["pendiente_verificacion"]);


/* ================= REDIRIGIR ================= */

header("Location: login.php?verified=1");
exit;