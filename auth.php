<?php

error_reporting(0);
ini_set('display_errors', 0);

if (!headers_sent()) {
    header("Content-Type: application/json");
}

if (!session_id()) {
    session_start();
}

/**
 * Obtener el token desde Authorization Bearer o request params.
 */
function getRequestToken(): ?string
{
    // Authorization: Bearer TOKEN
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        if (preg_match('/Bearer\s+(.*)$/i', trim($_SERVER['HTTP_AUTHORIZATION']), $matches)) {
            return trim($matches[1]);
        }
    }

    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        if (preg_match('/Bearer\s+(.*)$/i', trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']), $matches)) {
            return trim($matches[1]);
        }
    }

    if (isset($_REQUEST['token']) && trim($_REQUEST['token']) !== '') {
        return trim($_REQUEST['token']);
    }

    return null;
}

function jsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function getUserByToken(mysqli $db, string $token): ?array
{
    $stmt = $db->prepare("SELECT id, cedula, nombre, rol, placa, tipoVehiculo, zona FROM usuarios WHERE token = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result && $result->num_rows === 1 ? $result->fetch_assoc() : null;
    $stmt->close();
    return $user;
}

function requireAuth(array $allowedRoles = []): array
{
    global $conn, $conexion;

    if (!isset($conn) && isset($conexion)) {
        $conn = $conexion;
    }

    if (!isset($conexion) && isset($conn)) {
        $conexion = $conn;
    }

    if (!isset($conn) || !$conn) {
        jsonResponse(["success" => false, "message" => "Error interno de conexión"], 500);
    }

    $token = getRequestToken();
    if (!$token) {
        jsonResponse(["success" => false, "message" => "Token requerido"], 401);
    }

    $user = getUserByToken($conn, $token);
    if (!$user) {
        jsonResponse(["success" => false, "message" => "Token inválido"], 401);
    }

    $userRole = trim(strtolower($user['rol'] ?? ''));
    if (!empty($allowedRoles)) {
        $allowedNormalized = array_map(function ($role) {
            return trim(strtolower($role));
        }, $allowedRoles);

        if (!in_array($userRole, $allowedNormalized, true)) {
            jsonResponse(["success" => false, "message" => "No autorizado"], 403);
        }
    }

    $_SESSION['id_usuario']       = $user['id'];
    $_SESSION['cedula']           = $user['cedula'];
    $_SESSION['nombre']           = $user['nombre'];
    $_SESSION['rol']              = $userRole;
    $_SESSION['token']            = $token;
    $_SESSION['ultimo_movimiento'] = time();

    return $user;
}
