<?php
// Start session and check admin privileges
session_start();

// Database connection
require_once 'db_connect.php';

// Handle delete operation FIRST
if (isset($_POST['delete_offer'])) {
    $offer_id = intval($_POST['offer_id']); // prevent SQL injection

    // Use prepared statement for better security
    $stmt = $conn->prepare("DELETE FROM offers WHERE id = ?");
    $stmt->bind_param("i", $offer_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Offer deleted successfully";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting offer: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }
    
    $stmt->close();
    header("Location: admin_offers.php");
    exit();
}

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

// Handle form submission for adding new offers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_offer'])) {
    $bank_name = $_POST['bank_name'];
    $offer_text = $_POST['offer_text'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    // Get selected locations as an array and convert to comma-separated string
    $location_ids = implode(',', $_POST['location_ids']);
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO offers (bank_name, offer_text, start_date, end_date, location_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $bank_name, $offer_text, $start_date, $end_date, $location_ids);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Offer added successfully";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding offer: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }
    
    $stmt->close();
    header("Location: admin_offers.php");
    exit();
}

// Display messages if they exist
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Fetch existing offers - need to modify this query to handle multiple locations
$offers_query = "SELECT o.*, GROUP_CONCAT(l.name SEPARATOR ', ') as location_names 
                 FROM offers o 
                 LEFT JOIN locations l ON FIND_IN_SET(l.id, o.location_id) 
                 GROUP BY o.id 
                 ORDER BY o.start_date DESC";
$offers_result = $conn->query($offers_query);

// Fetch locations for checklist
$locations_query = "SELECT * FROM locations";
$locations_result = $conn->query($locations_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemaMax - Admin Offers</title>
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

        /* Admin specific styles */
        .admin-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .admin-title {
            font-size: 2rem;
            color: #333;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            background-color: #d21515;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background-color: #b01010;
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

        .locations-checklist {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .location-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .location-option input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .location-option label {
            margin: 0;
            font-weight: normal;
            cursor: pointer;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            font-size: 1.5rem;
            color: #333;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Chakra Petch', sans-serif;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        /* Style for the delete form container */
        .delete-offer-form {
            margin-top: 1.5rem;
        }
                
        /* Style for the delete button */
        .delete-btn {
            background-color: #ff3333;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: bold;
            transition: all 0.3s ease;
            width: 100%;
        }

        .delete-btn:hover {
            background-color: #cc0000;
            transform: translateY(-2px);
        }

        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-weight: bold;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
        <a href="admin_coming_soon.php" class="active">Coming Soon</a>
        <a href="Admin_Offers.php">Offers</a>
        <a href="AdminF&B.php">Food & Beverages</a>
    </nav>

    <main class="admin-container">
        <div class="admin-header">
            <h2 class="admin-title">Manage Offers</h2>
            <button id="addOfferBtn" class="btn">Add New Offer</button>
        </div>

        <?php if (isset($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="offers-grid">
            <?php if ($offers_result->num_rows > 0): ?>
                <?php while ($offer = $offers_result->fetch_assoc()): ?>
                    <div class="offer-card">
                        <img src="<?php echo getBankLogo($offer['bank_name']); ?>" alt="Bank Offer" class="offer-image">
                        <div class="offer-header">

                            <span class="offer-location"><?php echo htmlspecialchars($offer['location_names']); ?></span>
                        </div>
                        <div class="offer-body">
                            <p class="offer-text"><?php echo htmlspecialchars($offer['offer_text']); ?></p>
                            <div class="offer-dates">
                                <span>Start: <?php echo date('M d, Y', strtotime($offer['start_date'])); ?></span>
                                <span>End: <?php echo date('M d, Y', strtotime($offer['end_date'])); ?></span>
                            </div>
                            <form action="admin_offers.php" method="POST" class="delete-offer-form">
                                <input type="hidden" name="delete_offer" value="1">
                                <input type="hidden" name="offer_id" value="<?php echo $offer['id']; ?>">
                                <button type="submit" class="delete-btn" onclick="return confirm('delete this offer?')">
                                    Delete Offer
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No offers found.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Add Offer Modal -->
    <div id="addOfferModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Offer</h3>
                <button class="close-modal">&times;</button>
            </div>
            <form action="admin_offers.php" method="POST">
                <div class="form-group">
                    <label for="bank_name">Bank Name / Offer Supplier</label>
                    <input type="text" id="bank_name" name="bank_name" required>
                </div>
                <div class="form-group">
                    <label for="offer_text">Offer Text</label>
                    <textarea id="offer_text" name="offer_text" required></textarea>
                </div>
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>
                <div class="form-group">
                    <label>Locations</label>
                    <div class="locations-checklist">
                        <?php 
                        // Reset the pointer since we may have used locations_result earlier
                        $locations_result->data_seek(0); 
                        while ($location = $locations_result->fetch_assoc()): ?>
                            <div class="location-option">
                                <input type="checkbox" id="location_<?php echo $location['id']; ?>" 
                                       name="location_ids[]" value="<?php echo $location['id']; ?>">
                                <label for="location_<?php echo $location['id']; ?>">
                                    <?php echo htmlspecialchars($location['name']); ?>
                                </label>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn">Save Offer</button>
                </div>
            </form>
        </div>
    </div>

 

    <script>
        // Modal functionality
        const modal = document.getElementById('addOfferModal');
        const addBtn = document.getElementById('addOfferBtn');
        const closeBtns = document.querySelectorAll('.close-modal');

        addBtn.addEventListener('click', () => {
            modal.style.display = 'flex';
        });

        closeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                modal.style.display = 'none';
            });
        });

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            const checkedLocations = document.querySelectorAll('input[name="location_ids[]"]:checked');
            if (checkedLocations.length === 0) {
                e.preventDefault();
                alert('Please select at least one location');
            }
        });
    </script>
</body>
</html>