<?php
include_once "./../connector.php";
require '../vendor/autoload.php';
use \Firebase\JWT\JWT;

$secretKey = "xgemini";

function validateJWT($token) {
    global $secretKey;
    try {
        $decoded = JWT::decode($token, new \Firebase\JWT\Key($secretKey, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        echo json_encode(['status' => 401, 'message' => 'Invalid token']);
        return false;
    }
}

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    echo json_encode(["status" => 401, "message" => "Authorization token missing or invalid"]);
    exit;
}

$jwt = $matches[1];
$decoded = validateJWT($jwt);
if (!$decoded) {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $input = json_decode(file_get_contents("php://input"), true);
    $username = $input['username'] ?? '';
    $newPassword = $input['new_password'] ?? '';
    
    if (empty($username) || empty($newPassword)) {
        echo json_encode(["status" => 400, "message" => "Username and new password are required"]);
        exit;
    }

    
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE user SET passcode = ? WHERE usr_mail = ?");
    $stmt->bind_param("ss", $hashedPassword, $username);

    if ($stmt->execute()) {
        echo json_encode(["status" => 200, "message" => "Password updated successfully"]);
    } else {
        echo json_encode(["status" => 500, "message" => "Failed to update password"]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => 405, "message" => "Method not allowed"]);
}

$conn->close();
?>
