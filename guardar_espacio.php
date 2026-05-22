<?php
header("Content-Type: application/json");
include "conexion.php";
include "auth.php";

$user = requireAuth(["admin", "seguridad"]);
$data = json_decode(file_get_contents("php://input"), true);

$idEspacio = $data['idEspacio'] ?? null;
$cedula    = $data['cedula'] ?? null;

if (!$idEspacio || !$cedula) {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit;
}

// 🔐 VALIDAR QUE EL ESPACIO ESTÉ LIBRE
$check = $conn->query("SELECT estado FROM espacios WHERE id = '$idEspacio'");
$row = $check->fetch_assoc();

if ($row['estado'] === 'ocupado') {
    echo json_encode(["success" => false, "message" => "Espacio ya ocupado"]);
    exit;
}

// ⏱️ FECHAS
$horaInicio = new DateTime("now", new DateTimeZone("America/Bogota"));
$tiempoLimite = new DateTime("now", new DateTimeZone("America/Bogota"));
$tiempoLimite->modify("+15 minutes");

// ✅ ACTUALIZAR CON USUARIO
$sql = "UPDATE espacios 
        SET disponible = 0,
            estado = 'ocupado',
            cedula = '$cedula',
            horaInicio = '".$horaInicio->format('Y-m-d H:i:s')."',
            tiempoLimite = '".$tiempoLimite->format('Y-m-d H:i:s')."'
        WHERE id = '$idEspacio'";

if ($conn->query($sql)) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}

$conn->close();