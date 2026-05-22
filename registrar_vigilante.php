<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

include "conexion.php";
include "auth.php";

$user = requireAuth(["admin"]);

$data = json_decode(file_get_contents("php://input"), true);

$nombre = $data['nombre'];
$cedula = $data['cedula'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO vigilantes (nombre, cedula, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nombre, $cedula, $password);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}
?>