<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "./../connector.php";
require '../vendor/autoload.php';
use \Firebase\JWT\JWT;

header('Content-Type: application/json');
$secretKey = "xgemini"; 

if (!$conn) {
    echo json_encode(['status' => 500, 'message' => 'Database connection error']);
    exit;
}

switch ($_SERVER["REQUEST_METHOD"]) {
    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['username'], $data['password'])) {
            $username = $data['username'];
            $password = $data['password'];

            $sql = "SELECT id, usr_mail, passcode FROM user WHERE usr_mail = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();

                    // Verify password
                    if (password_verify($password, $user['passcode'])) {
                        $payload = [
                            'id' => $user['id'],
                            'usr_mail' => $user['usr_mail'],
                            'iat' => time(),
                            'exp' => time() + (10 * 365 * 24 * 60 * 60)
                        ];
                        $jwt = JWT::encode($payload, $secretKey, 'HS256');

                        echo json_encode([
                            'status' => 200,
                            'message' => 'Login successful',
                            'token' => $jwt
                        ]);
                    } else {
                        echo json_encode(['status' => 401, 'message' => 'Invalid password']);
                    }
                } else {
                    echo json_encode(['status' => 401, 'message' => 'User not found']);
                }
                $stmt->close();
            } else {
                echo json_encode(['status' => 500, 'message' => 'Failed to prepare SQL statement']);
            }
        } else {
            echo json_encode(['status' => 400, 'message' => 'Username and password are required']);
        }
        break;

    default:
        echo json_encode(['status' => 405, 'message' => 'Method Not Allowed']);
}

$conn->close();
?>
