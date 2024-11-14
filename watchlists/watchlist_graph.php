<?php
include_once "./../connector.php";
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];

            // Use prepared statements to avoid SQL injection
            $stmt = $conn->prepare("SELECT DATE(date_added) AS date, COUNT(*) AS movie_count 
                                    FROM watchlists 
                                    WHERE usr_mail = ? 
                                    GROUP BY DATE(date_added) 
                                    ORDER BY DATE(date_added) ASC");
            $stmt->bind_param("s", $id);  // "s" means string parameter

            $stmt->execute();
            $result = $stmt->get_result();

            $dates = [];
            $data = [];

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $dates[] = $row['date'];
                    $data[] = (int)$row['movie_count'];
                }

                $response = [
                    "background" => "red",
                    "series" => [
                        [
                            "name" => "Watchlist Count",
                            "data" => $data
                        ]
                    ],
                    "options" => [
                        "chart" => [
                            "height" => 350,
                            "type" => "area"
                        ],
                        "dataLabels" => [
                            "enabled" => false
                        ],
                        "stroke" => [
                            "curve" => "smooth"
                        ],
                        "xaxis" => [
                            "type" => "datetime",
                            "categories" => $dates
                        ],
                        "grid" => [
                            "show" => false
                        ],
                        "tooltip" => [
                            "x" => [
                                "format" => "dd/MM/yy"
                            ]
                        ]
                    ]
                ];

                echo json_encode($response);
            } else {
                echo json_encode(["status" => 500, "message" => "Failed to retrieve data"]);
            }

            $stmt->close();  // Close prepared statement
        } else {
            echo json_encode(["status" => 400, "message" => "ID parameter is missing"]);
        }
        break;
    default:
        echo json_encode(["status" => 405, "message" => "Method not allowed"]);
        break;
}

$conn->close();
?>
