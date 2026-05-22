<?php

header("Content-Type: application/json");

include "conexion.php";
include "auth.php";

$user = requireAuth(["admin", "seguridad"]);
$data = json_decode(file_get_contents("php://input"), true);

$usuarioId = $data["usuarioId"] ?? 0;
$espacioId = $data["espacioId"] ?? 0;

if (!$usuarioId || !$espacioId) {

    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos"
    ]);

    exit;
}

/* =========================
   VERIFICAR SI EL ESPACIO EXISTE
========================= */

$sqlEspacio = "
SELECT * FROM espacios
WHERE id = ?
LIMIT 1
";

$stmtEspacio = $conn->prepare($sqlEspacio);
$stmtEspacio->bind_param("i", $espacioId);
$stmtEspacio->execute();

$resultEspacio = $stmtEspacio->get_result();

if ($resultEspacio->num_rows <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Espacio no encontrado"
    ]);

    exit;
}

$espacio = $resultEspacio->fetch_assoc();

/* =========================
   VERIFICAR SI YA ESTÁ OCUPADO
========================= */

if ($espacio["estado"] !== "libre") {

    echo json_encode([
        "success" => false,
        "message" => "Espacio ocupado"
    ]);

    exit;
}

/* =========================
   ASIGNAR ESPACIO
========================= */

$sql = "
UPDATE espacios
SET
    estado = 'ocupado',
    disponible = 0,
    cedula = ?,
    horaInicio = NOW(),
    tiempoLimite = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $espacio["cedula"], $usuarioId, $espacioId);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Espacio asignado"
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Error al asignar"
    ]);

}
?>