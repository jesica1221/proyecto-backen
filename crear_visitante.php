<?php

header("Content-Type: application/json");
include "conexion.php";
include "auth.php";

$user = requireAuth(["admin", "seguridad"]);
$data = json_decode(file_get_contents("php://input"), true);

$placa = strtoupper(trim($data["placa"] ?? ""));

$cedulaTemporal = "VIS-" . time();

$nombre = "Visitante";

$sql = "
INSERT INTO usuarios
(nombre, cedula, placa, rol)
VALUES (?, ?, ?, 'visitante')
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sss",
    $nombre,
    $cedulaTemporal,
    $placa
);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "usuario" => [
            "nombre" => $nombre,
            "cedula" => $cedulaTemporal,
            "placa" => $placa,
            "rol" => "visitante"
        ]
    ]);

} else {

    echo json_encode([
        "success" => false
    ]);

}
?>