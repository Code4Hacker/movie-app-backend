<?php
include_once "./../connector.php";
header('Content-Type: application/json');

function addUser($conn, $username, $password) {
    
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    
    $sql = "INSERT INTO user (usr_mail, passcode) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $hashedPassword);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => 200, "message" => "Registration Successful!"]);
        return true;
    } else {
        echo json_encode(["status" => 500, "message" => $stmt->error]);
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!empty($data['username']) && !empty($data['password'])) {
        if (filter_var($data['username'], FILTER_VALIDATE_EMAIL)) {
            $result = addUser($conn, $data['username'], $data['password']);
        } else {
            echo json_encode(["status" => 400, "message" => "Invalid email format"]);
        }
    } else {
        echo json_encode(["status" => 402, "message" => "Username and password are required"]);
    }
}
$conn->close();
?>
