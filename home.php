<?php session_start() ;?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemaMax Home Page</title>
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

        .age-rating {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            background-color: #e74c3c;
            color: white;
            border-radius: 3px;
        }

        .hello {
            color: black;
            font-weight: bold;
            font-style: "Chakra Petch";
        }
        
        .fname {
            color: #d21515; 
            font-weight: bold;
        }

        nav {
            display: flex;
            align-items: center;
            gap: 1rem;
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
        <a href="coming_soon.html">Coming Soon</a>
        <a href="offers.html">Offers</a>
        <a href="f&b.html"> Food & Beverages</a>
        <a href="location.html"> Our Locations</a>
        <a href="#footer">Contact</a>
        <a href="login.html" style="padding: 10px 20px; background-color: #ff4444; color: white; text-decoration: none; border-radius: 5px; margin-left: auto;">Logout</a>

    </nav>

    <main>
        <section id="now-showing">
                <div style="margin-top: 2rem;"></div>
                <div style="margin-left: 2rem; margin-top: 2rem;">
                    <h2> <span class="hello">Hello </span><span class="fname"><?php echo $_SESSION['first_name']; ?>!</span> </h2>
                    <h3>
                        Check Out What's On:
                    
                    </h3>
                </div>
            </section>
            
            <script>
                function updateLocation() {
                    const select = document.getElementById('locationSelect');
                    const selectedLocation = document.getElementById('selectedLocation');
                    
                    if (select.value) {
                        selectedLocation.textContent = select.value;
                        select.style.display = 'none'; 
                        selectedLocation.style.display = 'inline'; 
                    }
                }
            
                function editLocation() {
                    const select = document.getElementById('locationSelect');
                    const selectedLocation = document.getElementById('selectedLocation');
            
                    select.style.display = 'inline'; 
                    selectedLocation.textContent = ''; 
                }

             
                async function loadMovies() {
                    const response = await fetch("movies.php");
                    const movies = await response.json();
                    const movieList = document.getElementById("movies");
                    movieList.innerHTML = "";

                    movies.forEach(movie => {
                        
                        movieList.innerHTML += `
                            <div class="movie-card">
                                <a href="bookingmovies.php?movie_id=${movie.id}">
                                <div class="movie-poster">
                                    <img src="movie%20posters/${movie.title}.jpg?ts=${Date.now()}" alt="${movie.title}">
                                </div>
                                <div class="movie-info">
                                    <h3>${movie.title}</h3>
                                    <p>Genre: ${movie.genre}</p>
                                    <p>Duration: ${movie.duration}</p>
                                    <span class="age-rating"> ${movie.rating}</span>
                                </div>
                                </a>
                            </div>
                        `;
                    });
                }

                loadMovies();


            </script>

                 
            <div class="movie-container" id="movies">
                
            </div>

        </section>

        
        

        
    </main>

    <footer id="footer">
        <div>
            <h3>Contact Us</h3>
            <p>HQ: Office 304 District 5, New Cairo City</p>
            <p>Hotline: 161676</p>
            <p>Email: info@cinemamax.com</p>
        </div>
        <div style="margin-top: 1rem;">
            <p>&copy; 2025 CinemaMax. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>


