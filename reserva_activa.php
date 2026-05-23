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
        AND tiempoLimite > NOW()
        LIMIT 1";

$result = $conn->query($sql);

$reserva = null;

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $reserva = [
        'numero' => $row['numero'],
        'zona' => $row['zonaId'],
        'horaVencimiento' => (new DateTime($row['tiempoLimite'], new DateTimeZone("America/Bogota")))->format('c'),
    ];
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