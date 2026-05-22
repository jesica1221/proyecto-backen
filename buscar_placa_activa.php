<?php

header("Content-Type: application/json");
include "conexion.php";
include "auth.php";

$user = requireAuth(["admin", "seguridad"]);
$data = json_decode(file_get_contents("php://input"), true);

$placa = strtoupper(trim($data["placa"] ?? ""));

$sql = "
SELECT
    u.nombre,
    u.cedula,
    u.placa,
    e.numero,
    e.zonaId
FROM espacios e
INNER JOIN usuarios u
ON e.usuario_id = u.id
WHERE u.placa = ?
AND e.estado IN ('ocupado', 'por vencer')
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $placa);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $usuario = $result->fetch_assoc();

    echo json_encode([
        "success" => true,
        "tieneParqueadero" => true,
        "usuario" => $usuario
    ]);

} else {

    echo json_encode([
        "success" => true,
        "tieneParqueadero" => false
    ]);

}
?>