<?php
// funciones.php

/**
 * Obtiene el número de faltas consecutivas de un estudiante
 */
function obtenerFaltasConsecutivas($db, $usuario_id, $limite = 3) {
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM (
            SELECT DISTINCT fecha FROM asistencias ORDER BY fecha DESC LIMIT :limite
        ) as f
        WHERE NOT EXISTS (
            SELECT 1 FROM asistencias a WHERE a.usuario_id = :id AND a.fecha = f.fecha
        )
    ");
    // PDO requiere bindValue para parámetros numéricos en LIMIT
    $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
    $stmt->bindValue(':id', (int)$usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

/**
 * Cuenta cuántos estudiantes están en riesgo de deserción
 */
function contarEstudiantesEnRiesgo($db, $limite = 3) {
    // Obtenemos las últimas X fechas de clase
    $sqlFechas = "SELECT DISTINCT fecha FROM asistencias ORDER BY fecha DESC LIMIT $limite";
    $fechas = $db->query($sqlFechas)->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($fechas)) return 0;

    $totalRiesgo = 0;
    // Buscamos alumnos que no tengan registro en NINGUNA de esas fechas
    $sqlAlumnos = "SELECT id FROM usuarios WHERE rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')";
    $alumnos = $db->query($sqlAlumnos)->fetchAll();

    foreach ($alumnos as $alumno) {
        if (obtenerFaltasConsecutivas($db, $alumno['id'], $limite) >= $limite) {
            $totalRiesgo++;
        }
    }
    return $totalRiesgo;
}

/**
 * Formatea el ID de estudiante (Ej: EST-00005)
 */
function formatearID($id) {
    return "EST-" . str_pad($id, 5, '0', STR_PAD_LEFT);
}
?>
