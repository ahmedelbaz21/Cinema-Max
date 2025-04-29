<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET["id"])) {
        $id = intval($_GET["id"]);
        $stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $movie = $result->fetch_assoc();
        echo json_encode($movie);
        $stmt->close();
    } else {
        $result = $conn->query("SELECT * FROM movies");
        $movies = [];
        while ($row = $result->fetch_assoc()) {
            $movies[] = $row;
        }
        echo json_encode($movies);
    }
    $conn->close();
    exit;
}



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
        $stmt->bind_param("ssssssi", $title, $genre, $duration, $rating, $cast, $description, $id);
    } else {
       
        $stmt = $conn->prepare("INSERT INTO movies (title, genre, duration, rating, cast, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $genre, $duration, $rating, $cast, $description);
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

if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
    } else {
        parse_str(file_get_contents("php://input"), $_DELETE);
        $id = intval($_DELETE["id"] ?? 0);
    }

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }

        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid movie ID"]);
    }

    $conn->close();
    exit;
}


?>