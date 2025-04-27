<?php
include "db_connect.php";

$movie_id = intval($_GET['movie_id']);
$location = $_GET['location']; 
$date = $_GET['date']; 


$first_show = strtotime("09:00");
$last_show = strtotime("23:00");


$stmt = $conn->prepare("SELECT duration FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([]);
    exit;
}

$movie = $result->fetch_assoc();
$duration = $movie['duration'];
$ad_time = 10;      
$clean_time = 30;   

$total_interval = ($duration + $ad_time + $clean_time) * 60;

$showtimes = [];
$current_time = $first_show;

while ($current_time + ($duration * 60) <= $last_show) {
    $showtimes[] = date("H:i", $current_time);
    $current_time += $total_interval;
}

echo json_encode($showtimes);
