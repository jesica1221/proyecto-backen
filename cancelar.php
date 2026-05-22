<?php

header("Content-Type: application/json");
include "conexion.php";include "auth.php";

$user = requireAuth(["admin", "seguridad"]);
$data = json_decode(file_get_contents("php://input"), true);

$cedula = $data["cedula"] ?? "";

if ($cedula == "") {
    echo json_encode([
        "success" => false,
        "message" => "Cédula requerida"
    ]);
    exit;
}

/* 🔥 LIBERAR ESPACIOS */
$sql = "UPDATE espacios 
        SET estado = 'libre', cedula = NULL 
        WHERE cedula = '$cedula'";

if ($conn->query($sql)) {

    /* 🔥 CERRAR RESERVA ACTIVA */
    $conn->query("UPDATE reservas SET estado = 'cancelada' WHERE cedula = '$cedula' AND estado = 'activa'");

    echo json_encode([
        "success" => true,
        "message" => "Reserva cancelada correctamente"
    ]);

} else {
    echo json_encode([
        "success" => false,
        "message" => "Error al cancelar"
    ]);
}

$conn->close();
?>