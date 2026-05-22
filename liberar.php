<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");
include "conexion.php";
include "auth.php";

$user = requireAuth(["admin", "seguridad"]);

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$espacio_id = $data["espacio_id"] ?? "";

$sql = "UPDATE espacios SET estado='libre' WHERE id='$espacio_id'";

$conn->query($sql);

echo json_encode([
    "success" => true
]);

$conn->close();
?>