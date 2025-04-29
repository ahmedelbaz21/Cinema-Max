<?php
session_start();
// Handle review submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_type']) && $_POST['form_type'] === 'submit_review') {
    $movieId = $_POST['movie_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Check if user already left a review for this movie
    $stmt = $conn->prepare("SELECT * FROM reviews WHERE user_id = ? AND movie_id = ?");
    $stmt->bind_param("ii", $userId, $movieId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing review
        $stmt = $conn->prepare("UPDATE reviews SET rating = ?, comment = ?, review_date = NOW() WHERE user_id = ? AND movie_id = ?");
        $stmt->bind_param("isii", $rating, $comment, $userId, $movieId);
        $stmt->execute();
    } else {
        // Insert new review
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, movie_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $userId, $movieId, $rating, $comment);
        $stmt->execute();
    }
    $stmt->close();

    // Refresh page
    header("Location: profile.php");
    exit();
}
?>