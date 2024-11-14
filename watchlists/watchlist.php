<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
function addWatchlist($conn, $id, $original_language, $original_title, $overview, $popularity, $poster_path, $release_date, $title, $video, $vote_average, $vote_count, $usr_mail) {
    $sql = "INSERT INTO watchlists (id, original_language, original_title, overview, popularity, poster_path, release_date, title, video, vote_average, vote_count, usr_mail) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssss", $id, $original_language, $original_title, $overview, $popularity, $poster_path, $release_date, $title, $video, $vote_average, $vote_count, $usr_mail);
    if ($stmt->execute()) {
        return true;
    } else {
        echo json_encode(["status" => 500, "message" => $stmt->error]);
        return false;
    }
}

function getWatchlists($conn, $usr_mail) {
    $sql = "SELECT * FROM watchlists WHERE usr_mail=? ORDER BY wid DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usr_mail);
    if ($stmt->execute()) {
        return $stmt->get_result();
    } else {
        return false;
    }
}

switch ($_SERVER["REQUEST_METHOD"]) {
    case "POST":
        $headers = apache_request_headers();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $jwt = $matches[1];
            $decoded = validateJWT($jwt);
            if ($decoded) {
                $data = json_decode(file_get_contents("php://input"), true);
                $result = addWatchlist($conn, $data['id'], $data['original_language'], $data['original_title'], $data['overview'], $data['popularity'], $data['poster_path'], $data['release_date'], $data['title'], $data['video'], $data['vote_average'], $data['vote_count'], $decoded->usr_mail);
                if ($result) {
                    echo json_encode(["status" => 200, "message" => $data["title"] . " added to your watchlists 😊"]);
                }
            }
        } else {
            echo json_encode(["status" => 401, "message" => "Authorization token missing or invalid"]);
        }
        break;

    case "GET":
        $headers = apache_request_headers();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $jwt = $matches[1];
            $decoded = validateJWT($jwt);
            if ($decoded) {
                $result = getWatchlists($conn, $decoded->usr_mail);
                if ($result) {
                    $arr = [];
                    while ($row = $result->fetch_assoc()) {
                        $arr[] = $row;
                    }
                    echo json_encode(["status" => 200, "results" => $arr]);
                } else {
                    echo json_encode(["status" => 500, "message" => "Failed to retrieve watchlists"]);
                }
            }
        } else {
            echo json_encode(["status" => 401, "message" => "Authorization token missing or invalid"]);
        }
        break;

    default:
        echo json_encode(['status' => 405, 'message' => 'Method Not Allowed']);
}

$conn->close();
?>
