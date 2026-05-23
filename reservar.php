<?php
header("Content-Type: application/json");
include "conexion.php";
include "auth.php";

$user = requireAuth();

$data = json_decode(file_get_contents("php://input"), true);

$idEspacio = $data['idEspacio'] ?? null;
$cedula = $data['cedula'] ?? null;

if (!$idEspacio || !$cedula) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

// 🔥 OBTENER EL ESPACIO PRIMERO PARA SABER LA ZONA Y NUMERO
$sqlEspacio = "SELECT * FROM espacios WHERE id = '$idEspacio' LIMIT 1";
$resultEspacio = $conn->query($sqlEspacio);
$espacio = $resultEspacio->fetch_assoc();

if (!$espacio) {
    echo json_encode(["success" => false, "message" => "Espacio no encontrado"]);
    exit;
}

// 🔥 TIEMPO CORRECTO
$horaInicio = new DateTime("now", new DateTimeZone("America/Bogota"));

$tiempoLimite = new DateTime("now", new DateTimeZone("America/Bogota"));
$tiempoLimite->modify("+15 minutes");

$sql = "UPDATE espacios 
        SET disponible = 0,
            estado = 'ocupado',
            horaInicio = '".$horaInicio->format('Y-m-d H:i:s')."',
            tiempoLimite = '".$tiempoLimite->format('Y-m-d H:i:s')."',
            cedula = '$cedula'
        WHERE id = '$idEspacio'";

if ($conn->query($sql)) {

    echo json_encode([
        "success" => true,
        "horaVencimiento" => $tiempoLimite->format('c'),
        "numero" => $espacio['numero'],
        "zona" => $espacio['zonaId']
    ]);

} else {
    echo json_encode([
        "success" => false,
        "error" => $conn->error
    ]);
}

$conn->close();
?>