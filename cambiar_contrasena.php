<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

include "conexion.php";

$cedula = trim($_POST['cedula'] ?? '');
$nuevaContrasena = trim($_POST['nuevaContrasena'] ?? '');

if (empty($cedula) || empty($nuevaContrasena)) {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit;
}

$stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE cedula = ?");
$stmt->bind_param("ss", $nuevaContrasena, $cedula);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Contraseña actualizada correctamente"]);
} else {
    echo json_encode(["success" => false, "message" => "Error al actualizar la contraseña"]);
}

$stmt->close();
$conn->close();
?>