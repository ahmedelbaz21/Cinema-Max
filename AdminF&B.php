<?php 
session_start();
include "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];

    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($price) || !is_numeric($price)) $errors[] = 'Valid price is required';


    if (empty($errors)) {
        try {
            // Handle file upload
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imagePath = $targetPath;
                }
            }

            // Insert into database
            $stmt = $pdo->prepare("INSERT INTO FB 
                (name, price, image_path) 
                VALUES (?, ?, ?)");
            
            $stmt->execute([
                $name,
                $price,
                $imagePath
            ]);

            $_SESSION['message'] = 'Item added successfully!';
            header('Location: admin_foodbev.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
$items = [];
try {
    $stmt = $pdo->query("SELECT * FROM cinema_items ORDER BY created_at DESC");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = 'Error fetching items: ' . $e->getMessage();
}
?> 


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemaMax - Admin Food & Beverage</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="common.css">
    <style>
        /* Admin-specific styles */
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .admin-form {
            background: #f5f5f5;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }
        
        .items-table th,
        .items-table td {
            padding: 1rem;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        .items-table th {
            background-color: #2c3e50;
            color: white;
        }
        
        .items-table img {
            max-width: 100px;
            height: auto;
        }
        
        .message {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
        }
        
        .error-message {
            background-color: #ffe6e6;
            color: #cc0000;
        }
        
        .success-message {
            background-color: #e6ffe6;
            color: #008000;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="home.html" class="icon-btn"><i class="fas fa-home"></i></a>
            <h1><span class="cinema">CINEMA</span><span class="max">MAX</span></h1>
            <a href="profile.html" class="icon-btn"><i class="fas fa-user"></i></a>
        </div>
    </header>

    <nav>
        <a href="AdminHome.html">Now Showing</a>
        <a href="Admin_coming_soon.html">Coming Soon</a>
        <a href="Adminoffers.html">Offers</a>
        <a href="AdminF&B.php">Food & Beverages</a>
        <a href="Adminlocation.html">Our Locations</a>
        <a href="#footer">Contact</a>
    </nav>

    <!-- Admin Content -->
    <main class="admin-container">
        <h2>Food & Beverage Management</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="message error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success-message">
                <p><?= htmlspecialchars($_SESSION['message']) ?></p>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <section class="admin-form">
            <h3>Add New Item</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Item Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Price:</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="Snacks">Snacks</option>
                        <option value="Drinks">Drinks</option>
                        <option value="Combos">Combos</option>
                        <option value="Desserts">Desserts</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="image">Item Image:</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-plus-circle"></i> Add Item
                </button>
            </form>
        </section>

        <section class="existing-items">
            <h3>Current Menu Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= htmlspecialchars($item['description']) ?></td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td><?= htmlspecialchars($item['category']) ?></td>
                        <td>
                            <?php if ($item['image_path']): ?>
                                <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                     alt="<?= htmlspecialchars($item['name']) ?>">
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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