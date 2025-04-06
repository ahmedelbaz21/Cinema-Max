<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cinema_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle GET request to fetch movies
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query("SELECT * FROM movies");
    $movies = [];

    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }

    echo json_encode($movies);
}

// Handle POST request to add/edit movies
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
    $title = $_POST["title"];
    $genre = $_POST["genre"];
    $duration = $_POST["duration"];
    $rating = $_POST["rating"];

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE movies SET title=?, genre=?, duration=?, rating=? WHERE id=?");
        $stmt->bind_param("ssssi", $title, $genre, $duration, $rating, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO movies (title, genre, duration, rating) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $genre, $duration, $rating);
    }

    $stmt->execute();
    echo "Success";
}

$conn->close();
?>
