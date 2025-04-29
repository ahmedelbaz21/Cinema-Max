<?php
include "db_connect.php";
session_start();


function checkMoviesReleasingTomorrow($conn) {
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $stmt = $conn->prepare("SELECT title FROM coming_movies WHERE release_date = ?");
    $stmt->bind_param("s", $tomorrow);
    $stmt->execute();
    $result = $stmt->get_result();
    $movies = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    if (!empty($movies)) {
        $movieTitles = array_column($movies, 'title');
        $notification = "ALERT: The following movies are releasing tomorrow: " . implode(", ", $movieTitles);
        
        
        $updateStmt = $conn->prepare("UPDATE Admins SET pending_notifications = ?");
        $updateStmt->bind_param("s", $notification);
        $updateStmt->execute();
        $updateStmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  
    if (isset($_POST['delete_id'])) {
        $id = intval($_POST['delete_id']);
        $stmt = $conn->prepare("DELETE FROM coming_movies WHERE id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Movie deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting movie: " . $conn->error;
        }
        $stmt->close();
        header("Location: admin_coming_movies.php");
        exit;
    }
    

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $title = trim($_POST['title']);
    $genre = trim($_POST['genre']);
    $duration = intval($_POST['duration']);
    $rating = trim($_POST['rating']);
    $release_date = $_POST['release_date'];

   
    if (empty($title) || empty($genre) || empty($duration) || empty($rating) || empty($release_date)) {
        $_SESSION['error'] = "All fields are required";
    } else {
        if ($id > 0) {
            
            $stmt = $conn->prepare("UPDATE coming_movies SET title=?, genre=?, duration=?, rating=?, release_date=? WHERE id=?");
            $stmt->bind_param("ssissi", $title, $genre, $duration, $rating, $release_date, $id);
        } else {
          
            $stmt = $conn->prepare("INSERT INTO coming_movies (title, genre, duration, rating, release_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiss", $title, $genre, $duration, $rating, $release_date);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = "Movie " . ($id > 0 ? "updated" : "added") . " successfully!";
        
            checkMoviesReleasingTomorrow($conn);
        } else {
            $_SESSION['error'] = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
    header("Location: admin_coming_movies.php");
    exit;
}


checkMoviesReleasingTomorrow($conn);

$movies = [];
$result = $conn->query("SELECT * FROM coming_movies ORDER BY release_date");
if ($result) {
    $movies = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}


$notification = null;
$notificationResult = $conn->query("SELECT pending_notifications FROM Admins LIMIT 1");
if ($notificationResult && $notificationResult->num_rows > 0) {
    $row = $notificationResult->fetch_assoc();
    $notification = $row['pending_notifications'];

    $conn->query("UPDATE Admins SET pending_notifications = NULL");
}
$notificationResult->free();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Coming Soon Movies</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="common.css">
    <style>   
        .notification-banner {
            background-color: red;
            color: white;
            padding: 15px 40px 15px 20px; 
            margin: 0 auto 20px;
            width: 90%;
            max-width: 1400px;
            text-align: center;
            font-weight: bold;
            position: relative;
            border-radius: 5px;
        }

        .notification-text {
            display: inline-block;
            padding-right: 20px; 
        }

        .close-notification {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            margin: 0;
            line-height: 1;
            width: 24px;
            height: 24px;
        } 
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
        }

        .movie-info {
            padding: 1rem;
        }

        .edit-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px;
            border-radius: 50%;
            cursor: pointer;
        }

        .add-movie-card {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 250px;
            border: 2px dashed #ddd;
            cursor: pointer;
            font-size: 2rem;
            color: #555;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            margin: 10% auto;
            width: 50%;
            border-radius: 10px;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
        }

        input, button {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            background-color: #d21515;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #b10e0e;
        }

        .age-rating {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            background-color: #e74c3c;
            color: white;
            border-radius: 3px;
        }
        .release-date{
            display: inline-block;
            padding: 0.2rem 0.5rem;
            background-color: #3d3b3b;
            color: white;
            border-radius: 3px;
            margin-top: 0.5rem;
        }
        .delete-icon {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(231, 76, 60, 0.9);
            color: white;
            padding: 5px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .delete-icon:hover {
            background: rgba(192, 57, 43, 0.9);
        }
    </style>
</head>
<body>
<header>
    <div class="header-container">
        <h1><span class="cinema">CINEMA</span><span class="max">MAX</span></h1>
    </div>
</header>

<nav>
    <a href="AdminHome.php">Now Showing</a>
    <a href="admin_coming_movies.php" class="active">Coming Soon</a>
    <a href="Admin_offers.php">Offers</a>
    <a href="AdminF&B.php">Food & Beverages</a>
</nav>

<div class="content-container">
    <?php if ($notification): ?>
        <div class="notification-banner" id="adminNotification">
        <span class="notification-text"><?php echo htmlspecialchars($notification); ?></span>
        <button type="button" class="close-notification" onclick="this.parentElement.remove()">×</button>
    </div>
    <?php endif; ?>
    <div class="movie-container" id="movies">
        <div class="movie-card" id="addMovie" onclick="openModal()">
            <div class="add-movie-card">+</div>
        </div>
    </div>
</div>

<div id="movieModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>Add Coming Soon Movie</h2>
        <form id="movieForm">
            <input type="hidden" id="movie_id">
            <input type="text" id="title" placeholder="Movie Title" required>
            <input type="text" id="genre" placeholder="Genre" required>
            <input type="number" id="duration" placeholder="Duration (minutes)" required>
            <input type="text" id="rating" placeholder="Age Rating (e.g., PG-13)" required>
            <input type="date" id="release_date" placeholder="Release Date" required>
            <button type="button" onclick="saveMovie()">Save Movie</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const movies = <?php echo json_encode($movies); ?>;
        renderMovies(movies);
    });

    function openModal() {
        document.getElementById("movieModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("movieModal").style.display = "none";
        document.getElementById("movieForm").reset();
    }

    function renderMovies(movies) {
        const movieList = document.getElementById("movies");
    
        movieList.innerHTML = `
            <div class="movie-card" id="addMovie" onclick="openModal()">
                <div class="add-movie-card">+</div>
            </div>
        `;

        movies.forEach(movie => {
            const movieCard = document.createElement("div");
            movieCard.classList.add("movie-card");
            movieCard.innerHTML = `
                <div class="movie-poster">
                    <img src="movie%20posters/${movie.title}.jpg?ts=${Date.now()}" alt="${movie.title}">
                </div>
                <div class="movie-info">
                    <h3>${movie.title}</h3>
                    <p>Genre: ${movie.genre}</p>
                    <p>Duration: ${Math.floor(movie.duration/60)}h ${movie.duration%60}min</p>
                    <span class="age-rating">${movie.rating}</span>
                    <span class="release-date"> Release: ${new Date(movie.release_date).toLocaleDateString()}</span>
                </div>
                <div class="edit-icon" onclick="editMovie(${movie.id})">✎</div>
                <div class="delete-icon" onclick="deleteMovie(${movie.id})">Delete</div>
            `;
            movieList.insertBefore(movieCard, document.getElementById("addMovie"));
        });
    }

    function saveMovie() {
        const id = document.getElementById("movie_id").value;
        const title = document.getElementById("title").value.trim();
        const genre = document.getElementById("genre").value.trim();
        const duration = document.getElementById("duration").value;
        const rating = document.getElementById("rating").value.trim();
        const release_date = document.getElementById("release_date").value;

        if (title && genre && duration && rating && release_date) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'admin_coming_movies.php';
            
            const fields = {
                id: id || '0',
                title: title,
                genre: genre,
                duration: duration,
                rating: rating,
                release_date: release_date
            };
            
            for (const [name, value] of Object.entries(fields)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        } else {
            alert("Please fill all fields completely!");
        }
    }

    function editMovie(id) {
        const movies = <?php echo json_encode($movies); ?>;
        const movie = movies.find(m => m.id == id);
        
        if (movie) {
            document.getElementById("movie_id").value = movie.id;
            document.getElementById("title").value = movie.title;
            document.getElementById("genre").value = movie.genre;
            document.getElementById("duration").value = movie.duration;
            document.getElementById("rating").value = movie.rating;
            document.getElementById("release_date").value = movie.release_date.split(' ')[0];
            openModal();
        } else {
            alert("Movie not found");
        }
    }
    
    function deleteMovie(id) {
        if (confirm("Are you sure you want to delete this movie?")) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'admin_coming_movies.php';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_id';
            input.value = id;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
</body>
</html>