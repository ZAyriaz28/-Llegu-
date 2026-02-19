<?php
require_once "config/auth.php";
require_once "config/db.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inicio = $_POST['inicio'] ?? '';
    $fin = $_POST['fin'] ?? '';

    if (empty($inicio) || empty($fin)) {
        echo json_encode(['error' => 'Debes seleccionar ambas fechas']);
        exit;
    }

    try {
        // Consulta con JOIN para traer los datos del estudiante
        $sql = "SELECT 
                    a.fecha, 
                    u.nombre, 
                    u.usuario, 
                    a.clase, 
                    a.registrado_en as hora 
                FROM asistencias a
                INNER JOIN usuarios u ON a.usuario_id = u.id
                WHERE a.fecha BETWEEN :inicio AND :fin
                ORDER BY a.fecha DESC, a.registrado_en DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':inicio' => $inicio, ':fin' => $fin]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($resultados);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
