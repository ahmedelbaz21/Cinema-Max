<?php
// Database connection
require_once 'db_connect.php';

// Function to get bank logo based on bank name
function getBankLogo($bank_name) {
    $bank_logos = [
        'CIB' => 'cib.jpg',
        'Banque Misr' => 'Banque_Misr.jpg',
        // Add more banks and their logos as needed
        'default' => 'default_bank.jpg' // Fallback image
    ];
    
    // Clean the bank name and check for matches
    $clean_name = trim($bank_name);
    foreach ($bank_logos as $key => $logo) {
        if (strcasecmp($clean_name, $key) === 0) {
            return $logo;
        }
    }
    
    return $bank_logos['default'];
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch offers
$sql = "SELECT o.*, GROUP_CONCAT(l.name SEPARATOR ', ') as location_names 
        FROM offers o 
        LEFT JOIN locations l ON FIND_IN_SET(l.id, o.location_id) 
        GROUP BY o.id 
        ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemaMax - Bank Offers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="common.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Chakra Petch', sans-serif;
        }

        header {
            background-color: #1a1a1a;
            color: white;
            padding: 1rem;
            text-align: center;
            font-style: "Chakra Petch";
        }

        nav {
            background-color: #333;
            padding: 1rem;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
        }

        footer {
            background-color: #1a1a1a;
            color: white;
            padding: 2rem;
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

        .icon-btn i {
            font-size: 1.2rem;
        }

        .icon-btn:hover {
            background-color: white;
            color: #1a1a1a;
        }

        .cinema {
            color: white;
            font-weight: bold;
            font-style: "Chakra Petch";
        }

        .max {
            color: #d21515;
            font-weight: bold;
        }

        /* Main content styles */
        .main-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .page-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 2rem;
            text-align: center;
        }

        .offers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .offer-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .offer-card:hover {
            transform: translateY(-5px);
        }

        .offer-header {
            background-color: #FFFFFF;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .offer-bank {
            font-weight: bold;
            font-size: 1.2rem;
        }

        .offer-location {
            background-color: #ff3333;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .offer-body {
            padding: 1.5rem;
        }

        .offer-text {
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .offer-dates {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .offer-image {
            width: 100%;
            height: 150px;
            object-fit: contain;
            background-color: #f5f5f5;
            border-bottom: 1px solid #ddd;
        }

        .no-offers {
            text-align: center;
            grid-column: 1 / -1;
            padding: 2rem;
            color: #666;
        }

        .offer-tag {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #d21515;
            color: white;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
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
    <main class="main-container">
        <h2 class="page-title">Bank Offers</h2>

        <div class="offers-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($offer = $result->fetch_assoc()): ?>
                    <div class="offer-card">
                        <img src="<?php echo getBankLogo($offer['bank_name']); ?>" alt="Bank Offer" class="offer-image">
                        <div class="offer-header">
                            
                            <span class="offer-location"><?php echo htmlspecialchars($offer['location_names'] ?? 'All Locations'); ?></span>
                        </div>
                        <div class="offer-body">
                            <p class="offer-text"><?php echo htmlspecialchars($offer['offer_text']); ?></p>
                            <div class="offer-dates">
                                <span>Valid from: <?php echo date('M d, Y', strtotime($offer['start_date'])); ?></span>
                                <span>Until: <?php echo date('M d, Y', strtotime($offer['end_date'])); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-offers">
                    <h3>No offers available</h3>
                    <p>Check later for new offers.</p>
                </div>
            <?php endif; ?>
        </div>
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

<?php
$conn->close();
?>