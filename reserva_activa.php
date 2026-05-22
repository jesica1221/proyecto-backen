<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");
include "conexion.php";
include "auth.php";

$user = requireAuth();

$data = json_decode(file_get_contents("php://input"), true);
$cedula = $data['cedula'] ?? null;

if (!$cedula) {
    echo json_encode(["success" => false]);
    exit;
}

// 🔥 BUSCAR RESERVA ACTIVA
$sql = "SELECT * FROM espacios 
        WHERE cedula = '$cedula'
        AND estado = 'ocupado'
        AND tiempoLimite > DATE_SUB(NOW(), INTERVAL 6 HOUR)
        LIMIT 1";

$result = $conn->query($sql);

$reserva = null;

while ($row = $result->fetch_assoc()) {
    $reserva = $row;
    // Calcular tiempo restante en base a la hora del servidor
    $reserva['remaining_seconds'] = strtotime($row['tiempoLimite']) - time();
}

// 🔥 RESPUESTA
if ($reserva) {
    echo json_encode([
        "success" => true,
        "reserva" => $reserva
    ]);
} else {
    echo json_encode(["success" => false]);
}

$conn->close();
?>