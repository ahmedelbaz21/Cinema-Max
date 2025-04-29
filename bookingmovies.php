<?php session_start();
include "db_connect.php";



$movie_id = intval($_GET['movie_id']);
if (!$movie_id) {
    die("No movie selected.");
}





$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Movie not found.");
}

$movie = $result->fetch_assoc();
$user_id = $_SESSION['user_id'];
$user_email = isset($_SESSION['email']) ? $_SESSION['email'] : 'N/A';
$stmt = $conn->prepare("SELECT * FROM cards WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cards = [];
while ($row = $result->fetch_assoc()) {
    $cards[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemaMax - Booking <?php echo htmlspecialchars($movie['title']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="common.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Chakra Petch', sans-serif;
        }

        body {
            background-color: #1a1a1a;
            color: white;
            padding: 20px;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #d21515;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background-color: #333;
            padding: 20px;
            border-radius: 10px;
            color: white;
        }

        .filters {
            text-align: center;
            margin-bottom: 20px;
        }

        .movie-info {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .movie-poster img {
            width: 200px;
            border-radius: 10px;
        }

        .movie-details {
            text-align: left;
            margin-top: 10px;
        }

        .showtimes {
            margin-top: 10px;
            text-align: center;
        }

        .showtimes button {
            background-color: #f0f0f0;
            color: black;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
            border: none;
        }

        .showtimes button.active {
            background-color: #d21515;
            color: white;
        }

        .seating-area {
            text-align: center;
            margin-top: 10px; /* Was 40px */
            padding-top: 0px; /* Was 10px */
        }

        .screen {
            background-color: #ccc;
            height: 30px;
            width: 45%;
            margin: 0 auto 20px auto;
            text-align: center;
            line-height: 30px;
            font-weight: bold;
            border-radius: 10px;
        }

        .seat {
            width: 30px;
            height: 30px;
            background-color: gray;
            margin: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            color: white;
        }

        .seat.selected {
            background-color: green;
        }

        .seat.occupied {
            background-color: red;
            cursor: not-allowed;
        }

        .row-label {
            display: inline-block;
            width: 30px;
            font-weight: bold;
            text-align: center;
        }

        .buy-button {
            display: none;
            background-color: #d21515;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
            cursor: pointer;
        }

        header {
            position: relative;
            width: 100%;
            margin-bottom: 20px;
        }

        .header-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .header-container h1 {
            font-size: 2em;
        }

        .cinema {
            color: white;
        }

        .max {
            color: #d21515;
        }

        .cast-member {
            display: block;
            margin: 5px 0;
        }

        .btn-checkout {
            width: 100%;
            padding: 1rem;
            background-color: #d21515;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 1rem;
        }

        #checkoutModal {
            color: black;
        }

        #checkoutModal .summary {
            background-color: #f9f9f9;
            color: #333;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .payment-method {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .payment-method label {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .payment-method select {
            padding: 10px;
            font-size: 1rem;
            width: 200px;
        }

    </style>
</head>
<body>
    <header>
        <button class="back-button" onclick="window.location.href='home.php'">&larr; Back</button>
        <div class="header-container">
            <h1><span class="cinema">CINEMA</span><span class="max">MAX</span></h1>
        </div>
    </header>

    <div class="container">

        <div class="movie-info">
            <div class="movie-poster">
                <?php
                    $posterFile = rawurlencode($movie['title']) . '.jpg';
                ?>
               <img src="movie%20posters/<?php echo $posterFile; ?>?ts=<?php echo time(); ?>" 

                alt="<?php echo htmlspecialchars($movie['title']); ?>"
                onerror="this.onerror=null; this.src='movie%20posters/default.jpg';">


                <div class="movie-details">
                    <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie['genre']); ?></p>
                    <p><strong>Duration:</strong> <?php echo htmlspecialchars($movie['duration']); ?> minutes</p>
                    <p><strong>Age Rating:</strong> <?php echo htmlspecialchars($movie['rating']); ?></p>
                    <p><strong>Cast:</strong> <?php
                        $cast = explode(',', $movie['cast']);
                        echo htmlspecialchars(trim($cast[0])); 
                    ?></p>
                    <?php
                        for ($i = 1; $i < count($cast); $i++) {
                            echo '<span class="cast-member">' . htmlspecialchars(trim($cast[$i])) . '</span>';
                        }
                    ?>
                </div>
            </div>
            <div>

                <h1><?php echo htmlspecialchars($movie['title']); ?></h1>
                <p>About the movie: <?php echo htmlspecialchars($movie['description']); ?></p>
                <div class="filters">
                    <select id="locationSelect">
                        <option value="">Select Location</option>
                        <option value="District 5 Mall">District 5 Mall</option>
                        <option value="Arkan Plaza">Arkan Plaza</option>
                        <option value="City Stars Mall">City Stars Mall</option>
                        <option value="Open Air Mall">Open Air Mall</option>
                    </select>

                    <label for="date" style="margin-left: 20px;">Date:</label>
                    <select id="date"></select>
                </div>
               
                <div class="showtimes" id="showtimes-container">
                    
                </div>
                <div class="seating-area">
                    <h2>Select Your Seats</h2>
                    <div class="screen">SCREEN</div>
                    <div id="seats"></div>
                    <p id="seat-message" style="margin-top:10px; color: #d21515;"></p>
                    <p id="total-amount" style="margin-top:10px; font-weight:bold;"></p>
                    <button id="buy-button" class="buy-button">Buy Tickets</button>
                </div>
            </div>
        </div>

    </div>

    <div id="checkoutModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color: rgba(0,0,0,0.6); z-index:999;">
        <div style="background:white; max-width:600px; margin:5% auto; padding:2rem; border-radius:10px; position:relative;">
            <span onclick="closeCheckout()" style="position:absolute; top:10px; right:20px; font-size:1.5rem; cursor:pointer;">&times;</span>
            <h2>Checkout</h2>
            <form id="checkoutForm" >
                
                <input type="hidden" name="movie_id" value="<?php echo $_GET['movie_id']; ?>">

                <div class="user-email" style="margin-bottom: 15px; font-size: 1.1rem;">
                    <strong>Email:</strong> <?php echo $user_email; ?>
                </div>

                <div class="payment-method">
                    <label for="cardSelect" style="display: inline-block; margin-right: 10px;">  <strong> Payment Method: </strong></label>
                    <select id="cardSelect" name="cardSelect" style="display: inline-block;">
                        <option value="">Select Payment Method</option>
                        <?php foreach ($cards as $card) { ?>
                            <option value="<?php echo $card['card_number']; ?>">
                                <?php echo "Card ending in " . substr($card['card_number'], -4); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>


                <div class="summary" id="orderSummary">
                    <script>
                        
                       

                        function updateTotal() {
                            const totalAmount = document.getElementById("total-amount");
                            const buyButton = document.getElementById("buy-button");
                            const totalSeats = countSelectedSeats();
                            const selectedLocation = document.getElementById("locationSelect").value;
                            const selectedCard = document.getElementById("cardSelect").value;
                            totalAmount.textContent = `Ticket price : 150 EGP | Total Amount: ${totalSeats * pricePerSeat} EGP`;
                            buyButton.style.display = totalSeats > 0 ? "inline-block" : "none";

                            buyButton.onclick = () => {
                               


                                const selectedSeats = [];
                                for (let i = 0; i < rows; i++) {
                                    for (let j = 0; j < cols; j++) {
                                        if (seatMap[selectedShowtime]?.[i]?.[j] === true) {
                                            selectedSeats.push(`${rowLabels[i]}${j + 1}`);
                                        }
                                    }
                                }
                                
                             

                                

                                const summaryHTML = `
                                <h3>Order Summary</h3>
                                    <p><strong>Movie:</strong> <?php echo addslashes($movie['title']); ?></p>
                                    <p><strong>Date:</strong> ${document.getElementById("date").value}</p>
                                    <p><strong>Time:</strong> ${selectedShowtime}</p>
                                    <p><strong>Location:</strong> ${selectedLocation}</p>
                                    <p><strong>Seats:</strong> ${selectedSeats.join(", ")}</p>
                                    <p><strong>Total Price:</strong> EGP ${selectedSeats.length * pricePerSeat}</p>
                                `;
                                document.getElementById("orderSummary").innerHTML = summaryHTML;

                                document.getElementById("checkoutModal").style.display = "block";
                            };

                        }

                        function closeCheckout() {
                            document.getElementById("checkoutModal").style.display = "none";
                        }

                    </script>
                </div>
                <button type="submit" id="place-order-btn" class="btn-checkout">Place Order</button>
               

            </form>
        </div>
    </div>


    <script>
        const rows = 5, cols = 8, pricePerSeat = 150, maxSeats = 8;
        const rowLabels = ["A", "B", "C", "D", "E"];
        let selectedShowtime = null;
        let seatMap = {};

        function renderSeats() {
            const seatsContainer = document.getElementById("seats");
            const seatMessage = document.getElementById("seat-message");
            const totalAmount = document.getElementById("total-amount");
            const buyButton = document.getElementById("buy-button");

            seatsContainer.innerHTML = "";
            seatMessage.textContent = "";
            totalAmount.textContent = "";

            for (let i = 0; i < rows; i++) {
                const rowDiv = document.createElement("div");
                const rowLabel = document.createElement("span");
                rowLabel.classList.add("row-label");
                rowLabel.textContent = rowLabels[i];
                rowDiv.appendChild(rowLabel);

                for (let j = 0; j < cols; j++) {
                    const seat = document.createElement("div");
                    seat.classList.add("seat");
                    seat.textContent = j + 1;

                    const seatStatus = seatMap[selectedShowtime]?.[i]?.[j];
                    if (seatStatus === "occupied") seat.classList.add("occupied");
                    else if (seatStatus === true) seat.classList.add("selected");

                    seat.addEventListener("click", () => {
                        if (seat.classList.contains("occupied")) return;
                        const selectedSeats = countSelectedSeats();
                        if (seat.classList.contains("selected")) {
                            seat.classList.remove("selected");
                            seatMap[selectedShowtime][i][j] = false;
                        } else {
                            if (selectedSeats >= maxSeats) {
                                seatMessage.textContent = "You can only select up to 8 seats per transaction.";
                                return;
                            }
                            seat.classList.add("selected");
                            seatMap[selectedShowtime][i][j] = true;
                            seatMessage.textContent = "";
                        }
                        updateTotal();
                    });

                    rowDiv.appendChild(seat);
                }
                seatsContainer.appendChild(rowDiv);
            }
        }

        function countSelectedSeats() {
            if (!selectedShowtime || !seatMap[selectedShowtime]) return 0;
            return seatMap[selectedShowtime].flat().filter(val => val === true).length;
        }

        

        window.onload = function () {
            const dateSelect = document.getElementById("date");
            const locationSelect = document.getElementById("locationSelect");
            const showtimesContainer = document.getElementById("showtimes-container");

            const today = new Date();
            for (let i = 0; i < 7; i++) {
                const date = new Date(today);
                date.setDate(today.getDate() + i);
                const option = document.createElement("option");
                option.value = date.toISOString().split("T")[0];
                option.text = date.toDateString();
                dateSelect.appendChild(option);
            }

            dateSelect.addEventListener("change", fetchShowtimes);
            locationSelect.addEventListener("change", fetchShowtimes);

            function fetchShowtimes() {
                const location = locationSelect.value;
                const date = dateSelect.value;
                const movieId = <?php echo $movie_id; ?>;

                if (!location || !date) return;

                fetch(`generate_showtimes.php?movie_id=${movieId}&location=${encodeURIComponent(location)}&date=${date}`)
                    .then(res => res.json())
                    .then(times => {
                        showtimesContainer.innerHTML = "";
                        selectedShowtime = null;
                        seatMap = {};

                        times.forEach(time => {
                            seatMap[time] = Array(rows).fill().map(() => Array(cols).fill(false));
                            const btn = document.createElement("button");
                            btn.textContent = time;
                            btn.addEventListener("click", () => {
                                document.querySelectorAll(".showtimes button").forEach(b => b.classList.remove("active"));
                                btn.classList.add("active");
                                selectedShowtime = time;

                                
                                seatMap[selectedShowtime] = Array(rows).fill().map(() => Array(cols).fill(false));

                               
                                fetch(`fetch_booked_seats.php?movie_id=${movieId}&location=${encodeURIComponent(location)}&date=${date}&showtime=${selectedShowtime}`)
                                    .then(res => res.json())
                                    .then(bookedSeats => {
                                        bookedSeats.forEach(seat => {
                                            const row = rowLabels.indexOf(seat[0]);
                                            const col = parseInt(seat.slice(1)) - 1;
                                            if (row >= 0 && col >= 0) {
                                                seatMap[selectedShowtime][row][col] = "occupied";
                                            }
                                        });
                                        renderSeats(); 
                                    });
                            });

                            showtimesContainer.appendChild(btn);
                        });
                    });
            }

            
            if (locationSelect.value && dateSelect.options.length > 0) {
                dateSelect.selectedIndex = 0;
                fetchShowtimes();
            }
        

            

            document.getElementById("checkoutForm").addEventListener("submit", function(e) {
            e.preventDefault(); 
            console.log("Form is being submitted");

            const selectedLocation = document.getElementById("locationSelect").value;
            const selectedDate = document.getElementById("date").value;
            const selectedCard = document.getElementById("cardSelect").value;

            const selectedSeats = [];
                for (let i = 0; i < rows; i++) {
                    for (let j = 0; j < cols; j++) {
                        if (seatMap[selectedShowtime]?.[i]?.[j] === true) {
                            selectedSeats.push(`${rowLabels[i]}${j + 1}`);
                        }
                    }
                }

                const orderData = {
                    movie_id: <?php echo $movie_id; ?>,
                    email: "<?php echo htmlspecialchars($user_email); ?>",
                    location: selectedLocation,
                    showtime: selectedShowtime,
                    date: selectedDate,
                    seats: selectedSeats,
                    amount_paid: selectedSeats.length * pricePerSeat,
                    card_number: selectedCard 
                };

                fetch("order_handeling.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(orderData)
                    })
                    .then(res => res.text()) 
                    .then(data => {
                        console.log("Raw response:", data); 
                        try {
                            const parsed = JSON.parse(data);
                            if (parsed.success) {
                                alert("Order placed successfully!");
                                window.location.href = "home.php";
                            } else {
                                alert("Failed: " + parsed.message);
                            }
                        } catch (e) {
                            console.error("JSON parse error:", e);
                            alert("Fetch error: " + e);
                        }
                });

            });
        };


        

    </script>
</body>
</html>
