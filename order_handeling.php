<?php
include 'db_connect.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;





$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$selectedCard = $data['card_number'] ?? '';


$movie_id = intval($data['movie_id']);
$location = $data['location'] ?? '';
$showtime = $data['showtime'] ?? '';
$date = $data['date'] ?? '';
$seats = is_array($data['seats']) ? implode(',', $data['seats']) : $data['seats'];
$amount_paid = floatval($data['amount_paid'] ?? 0);


$user_id = $_SESSION['user_id'] ?? 0;
$email = $_SESSION['email'];

$stmt = $conn->prepare("
    SELECT u.email, RIGHT(c.card_number, 4) AS last_four_digits
    FROM users u
    JOIN cards c ON u.id = c.user_id
    WHERE u.id = ? AND c.card_number = ?
    LIMIT 1
");

$stmt->bind_param("is", $user_id, $selectedCard);

$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $email = $row['email'];
    $payment_method = '**** **** **** ' . $row['last_four_digits'];
} else {
    $email = 'default@example.com';
    $payment_method = 'Unknown';
}
$stmt->close();

$movie_name = 'Unknown Movie';
$result = $conn->query("SELECT title FROM movies WHERE id = $movie_id");
if ($result && $result->num_rows > 0) {
    $movie = $result->fetch_assoc();
    $movie_name = $movie['title'];
}


$booking_datetime = "$date";


$stmt = $conn->prepare("INSERT INTO tickets (user_id, movie_id, payment_method, seats, amount_paid, booking_datetime, showtime, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }
$stmt->bind_param("iissdsss", $user_id, $movie_id, $payment_method, $seats, $amount_paid, $booking_datetime,  $showtime, $location);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Ticket insert failed: ' . $stmt->error]);
        exit;
    }
$stmt->close();
$conn->close();


$ticket_data = urlencode("Movie: $movie_name, DateTime: $booking_datetime, Seats: $seats, Location: $location");
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?data=$ticket_data&size=150x150";


$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'ahmednaserelbaz@gmail.com';       
    $mail->Password = 'vxfu haum ewgh ccfp';         
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('ahmednaserelbaz@gmail.com', 'Cinema Max'); 
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Your Cinema Ticket';

    $mail->Body = "
        <h3>Booking Confirmation</h3>
        <p><strong>Movie:</strong> $movie_name</p>
        <p><strong>Date:</strong> $booking_datetime</p>
        <p><strong>Time:</strong> $showtime </p>
        <p><strong>Seats:</strong> $seats</p>
        <p><strong>Location:</strong> $location</p>
        <p><strong>Amount Paid:</strong> EGP $amount_paid</p>
        <p><strong>Payment Method:</strong> $payment_method</p>
        <p><strong>QR Code for Check-In:</strong></p>
        <img src='$qr_code_url' alt='QR Code'>
    ";
    

    $mail->send();

    echo json_encode(['success' => true, 'message' => 'Order and email sent!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Email could not be sent: ' . $mail->ErrorInfo]);
}
?>
