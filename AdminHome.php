<?php
    include "db_connect.php";
    session_start();

    
    $result = $conn->query("SELECT * FROM movies");
    $movies = [];
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
    $conn->close();
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Manage Movies</title>
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
    <a href="Admin_coming_soon.html">Coming Soon</a>
    <a href="Adminoffers.html">Offers</a>
    <a href="AdminF&B.php">Food & Beverages</a>
</nav>

<div class="movie-container" id="movies">
    <!-- Add Movie Card -->
    <div class="movie-card" id="addMovie" onclick="openModal()">
        <div class="add-movie-card">+</div>
    </div>
</div>

<!-- Add Movie Modal -->
<div id="movieModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>Add Movie</h2>
        <form id="movieForm">
            <input type="hidden" id="movie_id">
            <input type="text" id="title" placeholder="Enter Movie Title" required>
            <input type="text" id="genre" placeholder="Enter Genre" required>
            <input type="text" id="duration" placeholder="Enter Duration in minutes (e.g. 120)" required>
            <input type="text" id="rating" placeholder="Enter Age Rating (e.g. PG-13)" required>
            <input type="text" id="cast" placeholder="Enter the cast of the movie" required>
            <input type="text" id="description" placeholder="Enter the movie's description" required>
            <button type="button" onclick="saveMovie()">Add Movie</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", fetchMovies);

    function openModal() {
        document.getElementById("movieModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("movieModal").style.display = "none";
        document.getElementById("movieForm").reset();
    }

    function fetchMovies() {
        fetch("movies.php")
            .then(res => res.json())
            .then(movies => {
                const movieList = document.getElementById("movies");

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
                            <p>Duration: ${movie.duration}</p>
                            <span class="age-rating"> ${movie.rating}</span>
                            
                        </div>
                        <div class="edit-icon" onclick="editMovie(${movie.id})">✎</div>
                    `;
                    movieList.insertBefore(movieCard, document.getElementById("addMovie"));
                });
            })
            .catch(err => {
                alert("Request failed.");
                console.error("Fetch error:", err);
            });

    }

    function saveMovie() {
        const id = document.getElementById("movie_id").value;
        const title = document.getElementById("title").value;
        const genre = document.getElementById("genre").value;
        const duration = document.getElementById("duration").value;
        const rating = document.getElementById("rating").value;
        const cast = document.getElementById("cast").value;
        const description = document.getElementById("description").value;

        if (title && genre && duration && rating && cast && description) {
            fetch("movies.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id, title, genre, duration, rating, cast, description })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    alert(id ? "Movie updated successfully!" : "Movie added successfully!");
                    closeModal();
                    document.getElementById("movies").innerHTML = `
                        <div class="movie-card" id="addMovie" onclick="openModal()">
                            <div class="add-movie-card">+</div>
                        </div>`;
                    fetchMovies(); // Refresh list
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(err => {
                alert("Request failed.");
                console.error(err);
            });
        } else {
            alert("Please fill all fields!");
        }
    }


    function editMovie(id) {
        fetch(`movies.php?id=${id}`)
            .then(res => res.json())
            .then(movie => {
                document.getElementById("movie_id").value = movie.id;
                document.getElementById("title").value = movie.title;
                document.getElementById("genre").value = movie.genre;
                document.getElementById("duration").value = movie.duration;
                document.getElementById("rating").value = movie.rating;
                document.getElementById("cast").value = movie.cast;
                document.getElementById("description").value = movie.description;

                openModal();
            })

            .catch(err => {
                alert("Failed to load movie details.");
                console.error("Fetch error:", err);
            });
        }
</script>

</body>
</html>
