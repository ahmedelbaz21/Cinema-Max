<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';

function deleteCard($conn, $cardId) {
    // Prepare the SQL DELETE query
    $stmt = $conn->prepare("DELETE FROM cards WHERE id = ?");
    $stmt->bind_param("i", $cardId);  // Bind the card ID
    $success = $stmt->execute();      // Execute the query
    $stmt->close();                   // Close the statement
    return $success;                  // Return success or failure
}

if (isset($_GET['delete_card_id'])) {
    $cardId = $_GET['delete_card_id'];

    if (deleteCard($conn, $cardId)) {
        header("Location: profile.php");
        exit();
    } else {
        echo "Error: Could not delete card.";
    }
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$userId = $_SESSION['user_id'];

// Handle adding new card
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_type']) && $_POST['form_type'] === 'add_card') {
    $cardNumber = $_POST['card_number'];
    $cardName = $_POST['card_name'];
    $expiry = $_POST['expiry'];
    $cvv = $_POST['cvv']; // Note: consider not saving CVV to database for security.

    $stmt = $conn->prepare("INSERT INTO cards (user_id, card_number, card_name, expiry, CVV) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $userId, $cardNumber, $cardName, $expiry, $cvv);
    $stmt->execute();
    $stmt->close();

    header("Location: profile.php");
    exit();
}


// Handle updating personal info
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fname'])) {
    $firstName = $_POST["fname"];
    $lastName = $_POST["lname"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    echo "Card form was submitted!";


    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $firstName, $lastName, $email, $phone, $userId);
    $stmt->execute();
    $stmt->close();
    if ($stmt->execute()) {
      echo "SUCCESS: Card inserted!";
  } else {
      echo "ERROR: " . $stmt->error;
  }

  $stmt->close();
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'submit_review') {
  $userId = $_SESSION['user_id'];
  $movie_id = intval($_POST['movie_id']);
  $rating = intval($_POST['rating']);
  $comment = trim($_POST['comment']);

  $insert_stmt = $conn->prepare("INSERT INTO reviews (user_id, movie_id, rating, comment) VALUES (?, ?, ?, ?)");
  $insert_stmt->bind_param("iiis", $userId, $movie_id, $rating, $comment);
  $insert_stmt->execute();
  $insert_stmt->close();

  // Refresh the page to show the new review
  header("Location: profile.php");
  exit();
}



// Fetch user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CinemaMax - Profile</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
  <link rel="stylesheet" href="common.css"/>
  <style>
    body {
      font-family: 'Chakra Petch', sans-serif;
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    header {
      background-color: #1a1a1a;
      color: white;
      padding: 1rem;
      text-align: center;
    }
    .header-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1400px;
      margin: 0 auto;
    }
    .icon-btn {
      color: white;
      text-decoration: none;
      padding: 0.8rem;
      border: 2px solid white;
      border-radius: 50%;
      transition: all 0.3s ease;
      width: 45px;
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .icon-btn:hover {
      background-color: white;
      color: #1a1a1a;
    }
    .cinema { color: white; font-weight: bold; }
    .max { color: #d21515; font-weight: bold; }
    .profile-container {
      max-width: 1000px;
      margin: 2rem auto;
      padding: 2rem;
    }
    .tabs {
      display: flex;
      border-bottom: 2px solid #ccc;
      margin-bottom: 2rem;
    }
    .tab {
      padding: 1rem 2rem;
      cursor: pointer;
      border: none;
      background: none;
      font-size: 1rem;
      border-bottom: 3px solid transparent;
    }
    .tab.active {
      border-bottom: 3px solid #d21515;
      color: #d21515;
    }
    .tab-content {
      display: none;
    }
    .tab-content.active {
      display: block;
    }
    .section-title {
      color: #d21515;
      margin-bottom: 1.5rem;
      border-bottom: 2px solid #d21515;
      padding-bottom: 0.5rem;
    }
    .form-group {
      margin-bottom: 1.5rem;
    }
    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: bold;
    }
    input {
      width: 100%;
      padding: 0.8rem;
      border: 1px solid #ddd;
      border-radius: 5px;
      margin-bottom: 0.5rem;
    }
    .edit-btn {
      background-color: #d21515;
      color: white;
      border: none;
      padding: 0.8rem 1.5rem;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    .edit-btn:hover {
      background-color: #b01010;
    }

    .credit-card-container {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .credit-card {
        width: 340px;
        height: 200px;
        background: linear-gradient(to bottom right,#d21515, #1a1a1a);
        color: white;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        padding: 20px;
        position: relative;
        overflow: hidden;
        font-family: Arial, sans-serif;
    }

    .credit-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-logo-img {
        width: 40px;
        height: auto;
    }

    .card-type {
        font-size: 14px;
        font-weight: bold;
    }

    .credit-card-body {
        margin-top: 40px;
        text-align: left;
    }

    .card-number {
        font-size: 22px;
        letter-spacing: 2px;
    }

    .card-name {
        margin-top: 10px;
        font-size: 16px;
        text-transform: uppercase;
    }

    .card-expiry {
        margin-top: 10px;
        font-size: 14px;
    }

    .credit-card:hover {
        transform: scale(1.05);
        transition: transform 0.3s ease;
    }

    
  </style>
</head>
<body>
    <!--header-->
  <header>
    <div class="header-container">
      <a href="home.php" class="icon-btn"><i class="fas fa-home"></i></a>
      <h1><span class="cinema">CINEMA</span><span class="max">MAX</span></h1>
      <a href="profile.php" class="icon-btn"><i class="fas fa-user"></i></a>
    </div>
  </header>

  <!--profile container-->
  <div class="profile-container">
    <div class="tabs">
      <button class="tab active" onclick="showTab('personal')">Personal Information</button>
      <button class="tab" onclick="showTab('payment')">Payment Methods</button>
      <button class="tab" onclick="showTab('history')">Purchase History</button>
    </div>
    

    <!--personal information-->

    <div id="personal" class="tab-content active">
      <h2 class="section-title">Personal Information</h2>
      <form method="POST" action="profile.php">
        <div class="form-group">
          <label for="fname">First Name</label>
          <input type="text" id="fname" name="fname" value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </div>
        <div class="form-group">
          <label for="lname">Last Name</label>
          <input type="text" id="lname" name="lname" value="<?= htmlspecialchars($user['last_name']) ?>" required>
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
        </div>
        <button type="submit" class="edit-btn">Save Changes</button>
      </form>
    </div>



    <!--payment methods-->
    <div id="payment" class="tab-content">
      <h2 class="section-title">Payment Methods</h2>
      <form method="POST" action="profile.php" class="form-group">
        <input type="hidden" name="form_type" value="add_card">

        <div style="display: flex; gap: 1rem;">
             <!-- card number -->
            <div class="form-group" style="flex: 1;">
                <label for="card_number">Card Number</label>
                <input type="text" id="card_number" name="card_number" placeholder="1234 1234 1234 1234" required>
            </div>

            <!-- card name -->
            <div class="form-group" style="flex: 1;">
                <label for="card_name">Name on Card</label>
                <input type="text" id="card_name" name="card_name" required>
            </div>

        </div>
       
        
        <div style="display: flex; gap: 1rem;">

          <!-- expiry date -->
          <div class="form-group" style="flex: 1;">
            <label for="expiry">Expiry Date</label>
            <input type="text" id="expiry" name="expiry" placeholder="MM/YY" required>
          </div>

          <!-- cvv -->
          <div class="form-group" style="flex: 1;">
            <label for="cvv">CVV</label>
            <input type="password" id="cvv" name="cvv" maxlength="3" required>
          </div>

        </div>
        <button type="submit" class="edit-btn">Add Card</button>
      </form>

      <div style="margin-top: 2rem;">
            <h3>Saved Cards</h3>
            <?php
            // Fetch saved cards for the current user
            $userId = $_SESSION['user_id'];
            $stmt = $conn->prepare("SELECT * FROM cards WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo '<div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: flex-start;">';
                while ($card = $result->fetch_assoc()) {
                    $lastFour = substr($card['card_number'], -4);
                    ?>
                    <div class="credit-card-container" style="margin-bottom: 0;">
                        <div class="credit-card">
                            <div class="credit-card-header">
                                <div class="card-logo">
                                    <img src="mastercard_logo.jpg.webp" alt="mastercard" class="card-logo-img">
                                </div>
                                <div class="card-type">Credit</div>
                            </div>
                            <div class="credit-card-body">
                                <div class="card-number">
                                    <span>**** **** **** </span><span><?= htmlspecialchars($lastFour) ?></span>
                                </div>
                                <div class="card-name">
                                    <span><strong><?= htmlspecialchars($card['card_name']) ?></strong></span>
                                </div>
                                <div class="card-expiry">
                                    <span><strong>Expires:</strong> <?= htmlspecialchars($card['expiry']) ?></span>
                                </div>
                                <div style="text-align: right; margin-top: 10px;">
                                    <form onsubmit="return confirm('Are you sure you want to delete this card?');" method="get" action="profile.php">
                                        <input type="hidden" name="delete_card_id" value="<?= $card['id'] ?>">
                                        <button type="submit" class="edit-btn" style="background-color: #ff4444; padding: 5px 10px; font-size: 0.9em;">
                                            Remove Card
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                echo '</div>';
            } else {
                echo "<p>No saved cards found.</p>";
            }
            $stmt->close();
            ?>
      </div>
    </div>
<div id="history" class="tab-content">
  <h2 class="section-title">Purchase History</h2>
  <?php
    $stmt = $conn->prepare("
      SELECT t.*, m.title AS movie_title, m.poster AS movie_poster 
      FROM tickets t 
      JOIN movies m ON t.movie_id = m.id 
      WHERE t.user_id = ? 
      ORDER BY t.booking_datetime DESC
    ");
  
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      echo "<div style='display: flex; flex-wrap: wrap; gap: 20px;'>";

      while ($row = $result->fetch_assoc()) {
        $movie = $row['movie_id'];
        $datetime = date("M d, Y - H:i", strtotime($row['booking_datetime']));
        $seats = htmlspecialchars($row['seats']);
        $location = htmlspecialchars($row['location']);
        $amount = number_format($row['amount_paid'], 2);
        $payment = htmlspecialchars($row['payment_method']);
        $movie_title = htmlspecialchars($row['movie_title']);
        $poster = rawurlencode($row['movie_title']) . '.jpg';

        $qr_data = urlencode("DateTime: {$datetime}, Seats: {$seats}, Location: {$location}");
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?data={$qr_data}&size=150x150";

        echo "
        <div style='display: flex; gap: 20px; border: 1px solid #ccc; border-radius: 10px; padding: 20px; margin-bottom: 20px; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>
          <div style='flex: 0 0 120px;'>
            <img src='movie%20posters/$poster' alt='Movie Poster' style='width: 120px; height: auto; border-radius: 8px;'>
          </div>
          <div style='flex: 1;'>
            <h3 style='margin-top: 0;'>🎬 $movie_title</h3>
            <p><strong> Date & Time:</strong> $datetime</p>
            <p><strong> Seats:</strong> $seats</p>
            <p><strong> Location:</strong> $location</p>
            <p><strong> Amount Paid:</strong> EGP $amount</p>
            <p><strong>Payment Method:</strong> $payment</p>
            <div>
              <strong> QR Code:</strong><br>
              <img src='$qr_url' alt='QR Code' style='margin-top: 8px;'>
            </div>
        ";

        // ✅ OUTSIDE echo, check reviews
        $review_stmt = $conn->prepare("SELECT * FROM reviews WHERE user_id = ? AND movie_id = ?");
        $review_stmt->bind_param("ii", $userId, $movie);
        $review_stmt->execute();
        $review_result = $review_stmt->get_result();
        $existing_review = $review_result->fetch_assoc();
        $review_stmt->close();

        if ($existing_review) {
          echo "
            <div style='margin-top: 10px;'>
              <p><strong>⭐ Your Rating:</strong> " . htmlspecialchars($existing_review['rating']) . "/5</p>
              <p><strong>📝 Comment:</strong> " . nl2br(htmlspecialchars($existing_review['comment'])) . "</p>
            </div>
          ";
        } else {
          echo "
            <form method='POST' action='profile.php' style='margin-top: 10px;'>
              <input type='hidden' name='form_type' value='submit_review'>
              <input type='hidden' name='movie_id' value='" . htmlspecialchars($movie) . "'>
              <div class='form-group'>
                <label for='rating_$movie'>Rating (1-5)</label><br>
                <input type='number' id='rating_$movie' name='rating' min='1' max='5' required style='width: 80px;'>
              </div>
              <div class='form-group' style='margin-top: 8px;'>
                <label for='comment_$movie'>Comment</label><br>
                <textarea id='comment_$movie' name='comment' rows='3' style='width: 100%;' required></textarea>
              </div>
              <button type='submit' style='margin-top: 10px; padding: 8px 12px; background-color: #d21515; color: white; border: none; border-radius: 4px;'>Submit Review</button>
            </form>
          ";
        }

        echo "
          </div> <!-- flex 1 close -->
        </div> <!-- ticket card close -->
        ";
      } // ✅ THIS closes the while loop correctly!

      echo "</div>"; // close flex-wrap container
    } else {
      echo "<p>You haven't booked any tickets yet.</p>";
    }

    $stmt->close();
  ?>
</div>


    <div style="text-align: center; margin-top: 2rem;">
      <a href="login.html" style="display: inline-block; padding: 10px 20px; background-color: #ff4444; color: white; text-decoration: none; border-radius: 5px;">Logout</a>
    </div>
  </div>

  <script>
    function showTab(tabId) {
      const tabs = document.querySelectorAll(".tab");
      const contents = document.querySelectorAll(".tab-content");

      tabs.forEach(tab => tab.classList.remove("active"));
      contents.forEach(content => content.classList.remove("active"));

      document.querySelector(`#${tabId}`).classList.add("active");
      event.target.classList.add("active");
    }
  </script>
</body>
</html>