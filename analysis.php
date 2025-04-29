<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';


$movie_id = $_GET['movie_id'] ?? null;

if (!$movie_id) {
    die("Movie ID is required.");
}


$movie_stmt = $conn->prepare("SELECT title, poster FROM movies WHERE id = ?");
if (!$movie_stmt) {
    die("Movie Info Query failed: " . $conn->error);
}
$movie_stmt->bind_param("i", $movie_id);
$movie_stmt->execute();
$movie_result = $movie_stmt->get_result();
$movie = $movie_result->fetch_assoc();


$stmt = $conn->prepare("SELECT COUNT(*) as total_tickets FROM tickets WHERE movie_id = ?");
if (!$stmt) {
    die("Sales Query failed: " . $conn->error);
}
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$sales_result = $stmt->get_result();
$sales_data = $sales_result->fetch_assoc();

$total_tickets = $sales_data['total_tickets'] ?? 0;
$ticket_price = 150;
$total_earned = $total_tickets * $ticket_price; 


$review_stmt = $conn->prepare("
    SELECT users.email AS user_email, reviews.rating, reviews.comment 
    FROM reviews 
    JOIN users ON reviews.user_id = users.id 
    WHERE reviews.movie_id = ?
");
if (!$review_stmt) {
    die("Reviews Query failed: " . $conn->error);
}
$review_stmt->bind_param("i", $movie_id);
$review_stmt->execute();
$reviews_result = $review_stmt->get_result();
?>

<style>
    body {
        background-color: #1a1a1a;
        color: white; 
    }
</style>


<div style="margin-top: 30px; padding: 20px; background: #222; border-radius: 10px; color: white; display: flex; align-items: flex-start; flex-wrap: wrap;">

 
    <?php if (!empty($movie['title'])): ?>
        <div style="flex-shrink: 0;">
            <img src="movie posters/<?php echo urlencode($movie['title']); ?>.jpg?ts=<?php echo time(); ?>"
                 alt="<?php echo htmlspecialchars($movie['title']); ?>"
                 style="max-height: 300px; border-radius: 10px; box-shadow: 0 0 10px #000; display: block; margin: 0;">
        </div>
    <?php else: ?>
        <p style="color: black;">Poster not available</p>
    <?php endif; ?>

   
    <div style="margin-left: 20px; min-width: 200px;">
        <h2 style="color: white; margin-top: 0; text-align: left; margin-bottom: 20px;">
            <?php echo htmlspecialchars($movie['title']); ?>
        </h2>
    </div>

    
    <div style="margin-left: 40px; flex: 1; min-width: 300px;">
        <h3 style="margin-top: 0;">Movie Analysis</h3>
        <p><strong>Total Tickets Sold:</strong> <?php echo $total_tickets; ?></p>
        <p><strong>Total Revenue:</strong> EGP <?php echo $total_earned; ?></p>

        <h3 style="margin-top: 30px;">User Reviews</h3>
        <?php if ($reviews_result->num_rows > 0): ?>
            <?php while ($review = $reviews_result->fetch_assoc()): ?>
                <div style="margin-bottom: 15px; border-bottom: 1px solid #555; padding-bottom: 10px;">
                    <p><strong><?php echo htmlspecialchars($review['user_email']); ?></strong> rated it: <?php echo $review['rating']; ?> ⭐</p>
                    <p><?php echo htmlspecialchars($review['comment']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No reviews yet.</p>
        <?php endif; ?>
    </div>

</div>

