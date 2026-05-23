<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

$servername = "localhost";
$username   = "root";
$passwordDB = "";
$dbname     = "eficientparkinglot";

$conn = new mysqli($servername, $username, $passwordDB, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión"]);
    exit;
}

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