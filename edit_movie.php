<?php
require_once 'db_connect.php';

// Get movie ID from URL
$movie_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($movie_id === 0) {
    header('Location: admin1.html');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $release_date = $_POST['release_date'];
    $duration = $_POST['duration'];
    $genre = $_POST['genre'];
    $trailer_url = $_POST['trailer_url'];
    $price = $_POST['price'];

    // Update movie in database
    $stmt = $conn->prepare("UPDATE movies SET title=?, description=?, release_date=?, duration=?, genre=?, trailer_url=?, price=? WHERE id=?");
    $stmt->bind_param("sssisssdi", $title, $description, $release_date, $duration, $genre, $trailer_url, $price, $movie_id);
    
    if ($stmt->execute()) {
        header('Location: admin1.html');
        exit();
    }
}

// Get current movie data
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

if (!$movie) {
    header('Location: admin1.html');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemaMax Admin - Edit Movie</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .movie-form {
            background: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: #fff;
            margin-bottom: 5px;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #444;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 5px;
        }
        .submit-btn {
            background: #e50914;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .submit-btn:hover {
            background: #f40612;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <h1>CinemaMax Admin</h1>
        </div>
        <div class="nav-links">
            <a href="admin1.html">Back to Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="admin-container">
        <h2 style="color: #fff;">Edit Movie</h2>
        
        <form class="movie-form" method="POST">
            <div class="form-group">
                <label for="title">Movie Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($movie['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Movie Description</label>
                <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($movie['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="release_date">Release Date</label>
                <input type="date" id="release_date" name="release_date" value="<?php echo $movie['release_date']; ?>" required>
            </div>

            <div class="form-group">
                <label for="duration">Duration (minutes)</label>
                <input type="number" id="duration" name="duration" value="<?php echo $movie['duration']; ?>" required>
            </div>

            <div class="form-group">
                <label for="genre">Genre</label>
                <input type="text" id="genre" name="genre" value="<?php echo htmlspecialchars($movie['genre']); ?>" required>
            </div>

            <div class="form-group">
                <label for="trailer_url">Trailer URL</label>
                <input type="url" id="trailer_url" name="trailer_url" value="<?php echo htmlspecialchars($movie['trailer_url']); ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Ticket Price</label>
                <input type="number" id="price" name="price" step="0.01" value="<?php echo $movie['price']; ?>" required>
            </div>

            <button type="submit" class="submit-btn">Update Movie</button>
        </form>
    </div>
</body>
</html> 