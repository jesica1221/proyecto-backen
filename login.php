<?php
/* =========================
   Configuración de sesión
========================= */
ini_set('session.gc_maxlifetime', 600); // 10 minutos totales
session_set_cookie_params(600);
session_start();

error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");

/* =========================
   Validar método HTTP
========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Método no permitido"
    ]);
    exit;
}

/* =========================
   Conexión a la base de datos
========================= */
include "conexion.php";

/* =========================
   Recibir datos
========================= */
$cedula   = trim($_POST['cedula'] ?? '');
$password = trim($_POST['password'] ?? '');

/* =========================
   Validar datos
========================= */
if (empty($cedula) || empty($password)) {
    echo json_encode([
        "success" => false,
        "message" => "Faltan datos"
    ]);
    exit;
}

/* =========================
   Consulta segura
========================= */
$stmt = $conn->prepare(
    "SELECT * FROM usuarios WHERE cedula = ? AND password = ?"
);

$stmt->bind_param("ss", $cedula, $password);
$stmt->execute();

$result = $stmt->get_result();

/* =========================
   Procesar respuesta
========================= */
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();

    /* =========================
       Generar token seguro
    ========================= */
    $token = bin2hex(random_bytes(32));

    /* =========================
       Guardar datos en sesión
    ========================= */
    $_SESSION['id_usuario']       = $user['id'];
    $_SESSION['cedula']           = $user['cedula'];
    $_SESSION['nombre']           = $user['nombre'];
    $_SESSION['rol']              = trim(strtolower($user['rol']));
    $_SESSION['token']            = $token;
    $_SESSION['ultimo_movimiento'] = time();

    /* =========================
       Guardar token en BD
       (requiere columna token en usuarios)
    ========================= */
    $stmtToken = $conn->prepare(
        "UPDATE usuarios SET token = ? WHERE id = ?"
    );
    $stmtToken->bind_param("si", $token, $user['id']);
    $stmtToken->execute();
    $stmtToken->close();

    /* =========================
       Registrar evento LOGIN
       (requiere tabla logs_sesion)
    ========================= */
    $ip = $_SERVER['REMOTE_ADDR'];

    $stmtLog = $conn->prepare(
        "INSERT INTO logs_sesion (id_usuario, token, accion, ip)
         VALUES (?, ?, 'LOGIN', ?)"
    );
    $stmtLog->bind_param("iss", $user['id'], $token, $ip);
    $stmtLog->execute();
    $stmtLog->close();

    /* =========================
       Respuesta exitosa
    ========================= */
    echo json_encode([
        "success" => true,
        "token"   => $token,
        "user" => [
            "id"            => $user['id'],
            "cedula"        => $user['cedula'],
            "nombre"        => $user['nombre'],
            "placa"         => $user['placa'],
            "tipoVehiculo"  => $user['tipoVehiculo'],
            "rol"           => trim(strtolower($user['rol'])),
            "zona"          => $user['zona']
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Credenciales incorrectas"
    ]);
}

/* =========================
   Cerrar conexión
========================= */
$stmt->close();
$conn->close();
?>