<?php 
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

include "conexion.php";
include "auth.php";

$user = requireAuth();

$conexion = $conn;

if ($conexion->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Error de conexión"
    ]);
    exit;
}

// 🔥 LIBERAR ESPACIOS VENCIDOS
$conexion->query("
    UPDATE espacios 
    SET disponible = 1,
        estado = 'libre',
        horaInicio = NULL,
        tiempoLimite = NULL,
        cedula = NULL
    WHERE tiempoLimite IS NOT NULL 
    AND tiempoLimite < NOW()
");

// 🔥 RECIBIR JSON
$data = json_decode(file_get_contents("php://input"), true);

// 🔥 NORMALIZAR
$zona = isset($data['zona']) ? intval($data['zona']) : null;
$tipoVehiculo = isset($data['tipoVehiculo']) 
    ? strtolower(trim($data['tipoVehiculo'])) 
    : null;

// 🔥 VALIDACIÓN CORRECTA
if ($zona === null || $tipoVehiculo === null || $tipoVehiculo === "") {
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos",
        "debug" => $data
    ]);
    exit;
}

// 🔥 QUERY
$sql = "SELECT * FROM espacios 
        WHERE zonaId = ? 
        AND tipoVehiculo = ?
        ORDER BY numero ASC";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "error_sql" => $conexion->error
    ]);
    exit;
}

$stmt->bind_param("is", $zona, $tipoVehiculo);
$stmt->execute();

$resultado = $stmt->get_result();

$espacios = [];

while ($row = $resultado->fetch_assoc()) {
    $espacios[] = $row;
}

echo json_encode([
    "success" => true,
    "espacios" => $espacios
]);

$stmt->close();
$conexion->close();
?>