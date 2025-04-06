<?php
require_once 'db_connect.php';

// Fetch all movies from the database
$sql = "SELECT * FROM movies ORDER BY release_date DESC";
$result = $conn->query($sql);

$movies = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $movies[] = array(
            'id' => $row['id'],
            'title' => $row['title'],
            'genre' => $row['genre'],
            'duration' => $row['duration'],
            'poster_url' => $row['poster_url']
        );
    }
}

// Return movies as JSON
header('Content-Type: application/json');
echo json_encode($movies);
?> 