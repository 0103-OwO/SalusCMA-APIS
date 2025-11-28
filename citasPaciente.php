<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

require_once("conexion.php");
ob_clean();

try {
    if (!isset($_GET["id"]) || empty($_GET["id"])) {
        echo json_encode([
            "success" => false,
            "error" => "ID de paciente requerido",
            "citas" => [],
            "total" => 0
        ]);
        exit;
    }

    $id = intval($_GET["id"]);

    $sql = "SELECT 
                c.id_cita,
                c.fecha,
                c.hora,
                c.id_paciente,
                c.id_medico,
                c.id_consultorio,
                p.curp AS curp_paciente,
                p.nombre AS nombre_paciente,
                CONCAT(t.nombre, ' ', t.apellido_paterno, ' ', t.apellido_materno) AS nombre_medico,
                co.nombre AS consultorio
            FROM citas c
            INNER JOIN pacientes p ON c.id_paciente = p.id_pacientes
            INNER JOIN trabajadores t ON c.id_medico = t.id_trabajador
            INNER JOIN consultorio co ON c.id_consultorio = co.id_consultorio
            WHERE c.id_paciente = ?
            ORDER BY c.fecha ASC, c.hora ASC";

    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error en la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    $citas = [];
    while ($row = $res->fetch_assoc()) {
        $citas[] = $row;
    }

    echo json_encode([
        "success" => true,
        "citas" => $citas,
        "total" => count($citas)
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => "Error del servidor: " . $e->getMessage(),
        "citas" => [],
        "total" => 0
    ]);
}

ob_end_flush();
?>