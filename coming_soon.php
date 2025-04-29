<?php
include "db_connect.php";
session_start();


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["title"], $data["genre"], $data["duration"], $data["rating"])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid input"]);
        exit;
    }

    $title = $conn->real_escape_string($data["title"]);
    $genre = $conn->real_escape_string($data["genre"]);
    $duration = $conn->real_escape_string($data["duration"]);
    $rating = $conn->real_escape_string($data["rating"]);
    $cast = isset($data["cast"]) ? $conn->real_escape_string($data["cast"]) : "";
    $description = isset($data["description"]) ? $conn->real_escape_string($data["description"]) : "";

    if (!empty($data["id"])) {
       
        $id = intval($data["id"]);
        $stmt = $conn->prepare("UPDATE movies SET title = ?, genre = ?, duration = ?, rating = ?, cast = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $title, $genre, $duration, $rating, $release_date, $id);
    } else {
       
        $stmt = $conn->prepare("INSERT INTO coming_movies (title, genre, duration, rating, relase_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssd", $title, $genre, $duration, $rating, $release_date);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit;
}




if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['api'])) {
    include "db_connect.php";
    

    $result = $conn->query("SELECT * FROM coming_movies ORDER BY release_date ASC");
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $conn->error]);
        exit;
    }
    
    $movies = [];
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
    

    header('Content-Type: application/json');
    echo json_encode([
        "status" => "success",
        "data" => $movies
    ]);
    exit;
}
?>