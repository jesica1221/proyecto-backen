<?php

header("Content-Type: application/json");
include "conexion.php";
include "auth.php";

$user = requireAuth(["admin", "seguridad"]);
$data = json_decode(file_get_contents("php://input"), true);

$placa = strtoupper(trim($data["placa"] ?? ""));
$cedula = trim($data["cedula"] ?? "");

if ($placa) {
    $sql = "SELECT * FROM usuarios WHERE placa = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $placa);
} else if ($cedula) {
    $sql = "SELECT * FROM usuarios WHERE cedula = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cedula);
} else {
    echo json_encode([
        "success" => true,
        "registrado" => false
    ]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    echo json_encode([
        "success" => true,
        "registrado" => true,
        "usuario" => $usuario
    ]);
} else {
    echo json_encode([
        "success" => true,
        "registrado" => false
    ]);
}
?>