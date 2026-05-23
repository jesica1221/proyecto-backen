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
   OBTENER EL USUARIO POR ID
========================= */
$sqlUsuario = "SELECT * FROM usuarios WHERE id = ? LIMIT 1";
$stmtUsuario = $conn->prepare($sqlUsuario);
$stmtUsuario->bind_param("i", $usuarioId);
$stmtUsuario->execute();
$resultUsuario = $stmtUsuario->get_result();

if ($resultUsuario->num_rows <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Usuario no encontrado"
    ]);
    exit;
}

$usuario = $resultUsuario->fetch_assoc();

/* =========================
   VERIFICAR SI EL USUARIO YA TIENE UN PARQUEADERO
========================= */
$sqlVerificarReserva = "
SELECT * FROM espacios WHERE cedula = ? AND estado = 'ocupado' AND tiempoLimite > NOW() LIMIT 1";
$stmtVerificar = $conn->prepare($sqlVerificarReserva);
$stmtVerificar->bind_param("s", $usuario["cedula"]);
$stmtVerificar->execute();
$resultVerificar = $stmtVerificar->get_result();

if ($resultVerificar->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "El usuario ya tiene un parqueadero asignado"
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
$stmt->bind_param("si", $usuario["cedula"], $espacioId);

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