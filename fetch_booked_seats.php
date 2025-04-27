<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);
include "db_connect.php";

$movie_id = intval($_GET['movie_id']);
$date = $_GET['date'];
$showtime = $_GET['showtime'];
$location = $_GET['location'];

$stmt = $conn->prepare("SELECT seats FROM tickets WHERE movie_id = ? AND location = ? AND date(booking_datetime) = ? AND showtime = ?");
if (!$stmt) {
    echo json_encode(["error" => "Prepare failed: " . $conn->error]);
    exit;
}
$stmt->bind_param("isss", $movie_id, $location, $date, $showtime);


$stmt->execute();
$result = $stmt->get_result();

$bookedSeats = [];

while ($row = $result->fetch_assoc()) {
    $seats = explode(",", $row['seats']);
    foreach ($seats as $seat) {
        $bookedSeats[] = trim($seat);
    }
}

echo json_encode($bookedSeats);
?>
