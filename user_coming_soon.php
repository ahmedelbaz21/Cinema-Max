<?php
include "db_connect.php";

// Fetch all movies
$result = $conn->query("SELECT * FROM coming_movies ORDER BY release_date ASC");
$movies = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Soon Movies</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="common.css">
    <style>
        .movie-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            padding: 2rem;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .movie-card {
            position: relative;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            width: 100%;
            transition: transform 0.3s ease-in-out;
        }

        .movie-poster img {
            width: 100%;
            height: auto;
            min-height: 375px;
            object-fit: cover;
        }

        .movie-info {
            padding: 1rem;
        }

        .age-rating {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            background-color: #e74c3c;
            color: white;
            border-radius: 3px;
        }
        
        .release-date {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            background-color: #3d3b3b;
            color: white;
            border-radius: 3px;
            margin-top: 0.5rem;
        }
        
        .no-movies {
            grid-column: 1 / -1;
            text-align: center;
            padding: 2rem;
            font-size: 1.2rem;
            color: #666;
        }
    </style>
</head>
<body>
    <header>
            <div class="header-container">
                <a href="home.php" class="icon-btn"><i class="fas fa-home"></i></a>
                <h1><span class="cinema">CINEMA</span><span class="max">MAX</span></h1>
                <a href="profile.php" class="icon-btn"><i class="fas fa-user"></i></a>
            </div>
    </header>

    <nav>
        <a href="home.php">Now Showing</a>
        <a href="user_coming_soon.php">Coming Soon</a>
        <a href="offers.php">Offers</a>
        <a href="f&b.html"> Food & Beverages</a>
        <a href="location.html"> Our Locations</a>
        <a href="#footer">Contact</a>
        <a href="login.html" style="padding: 10px 20px; background-color: #ff4444; color: white; text-decoration: none; border-radius: 5px; margin-left: auto;">Logout</a>

    </nav>


<div class="movie-container">
    <?php if (empty($movies)): ?>
        <div class="no-movies">No upcoming movies scheduled yet. Check back soon!</div>
    <?php else: ?>
        <?php foreach ($movies as $movie): ?>
        <div class="movie-card">
            <div class="movie-poster">
                <img src="movie%20posters/<?php echo $movie['title']; ?>.jpg" 
                     alt="<?php echo htmlspecialchars($movie['title']); ?>"
                     onerror="this.onerror=null;this.src='movie-posters/default.jpg';">
            </div>
            <div class="movie-info">
                <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                <p>Genre: <?php echo htmlspecialchars($movie['genre']); ?></p>
                <p>Duration: <?php echo floor($movie['duration']/60); ?>h <?php echo $movie['duration']%60; ?>min</p>
                <span class="age-rating"><?php echo htmlspecialchars($movie['rating']); ?></span>
                <span class="release-date">Release: <?php echo date('m/d/Y', strtotime($movie['release_date'])); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>