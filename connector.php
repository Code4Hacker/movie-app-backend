<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PATCH, GET, DELETE");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization");
header("Access-Control-Max-Age: 3600");
$servername = "mysql.z.silicaport.com";
$username = "graffitisda";
$password = "16Psyche";
$database = "zbase";


$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>