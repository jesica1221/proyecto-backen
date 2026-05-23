<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

include "conexion.php";

$cedula = trim($_POST['cedula'] ?? '');

if (empty($cedula)) {
    echo json_encode(["success" => false, "message" => "Falta la cédula"]);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE cedula = ? LIMIT 1");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    echo json_encode(["success" => true, "message" => "Usuario encontrado"]);
} else {
    echo json_encode(["success" => false, "message" => "No existe un usuario con esa cédula"]);
}

$stmt->close();
$conn->close();
?>