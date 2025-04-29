<?php
session_start();
include "db_connect.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 
// Example PDO connection — place this before you use $pdo
try {
  $pdo = new PDO("mysql:host=localhost;dbname=cinematrial", "root", "");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = trim($_POST['price']);

    $errors = [];
    if (empty($name)) $errors[] = "Product name is required.";
    if (empty($category)) $errors[] = "Category is required.";
    if (empty($price) || !is_numeric($price)) $errors[] = "Valid price is required.";

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO fb (name, category, price) VALUES (?, ?, ?)");
            $stmt->execute([$name, $category, $price]);
            $_SESSION['message'] = "Product '$name' added successfully!";
            header("Location: P6.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}


}



try {
    $stmt = $pdo->query("SELECT * FROM fb ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Failed to fetch products: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manage Food & Beverages | Cinema Max</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="common.css" />

  <style>
    /* Container grid similar to movie-container */
    .fnb-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      padding: 2rem;
      max-width: 1400px;
      margin: 0 auto;
    }

    /* Cards similar to movie-card */
    .fnb-card {
      position: relative;
      border: 1px solid #ddd;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transition: transform 0.3s ease-in-out;
      background: #fff;
      width: 100%;
    }

    /* Add button card */
    .add-fnb-card {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 200px;
      font-size: 3rem;
      border: 2px dashed #ddd;
      color: #555;
      cursor: pointer;
      border-radius: 8px;
    }

    /* Info inside card */
    .fnb-info {
      padding: 1rem;
    }

    .edit-icon {
      position: absolute;
      top: 10px;
      right: 10px;
      background: rgba(0, 0, 0, 0.7);
      color: white;
      padding: 6px 9px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 1.1rem;
      user-select: none;
    }

    /* Modal styles reused from AdminHome */
    .modal {
      display: none; /* Hidden by default */
      position: fixed;
      z-index: 1000;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
      background-color: #fff;
      padding: 20px;
      margin: 10% auto;
      width: 400px;
      border-radius: 10px;
      position: relative;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .close-btn {
      position: absolute;
      top: 10px; right: 15px;
      font-size: 24px;
      cursor: pointer;
      user-select: none;
    }

    /* Form inputs and buttons */
    input, select, button {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border-radius: 5px;
      border: 1px solid #ddd;
      font-size: 16px;
      box-sizing: border-box;
    }

    button {
      background-color: #d21515;
      color: white;
      border: none;
      cursor: pointer;
      transition: background-color 0.3s ease-in-out;
    }

    button:hover {
      background-color: #b10e0e;
    }

    /* Messages */
    .message {
      max-width: 1400px;
      margin: 1rem auto;
      padding: 15px;
      border-radius: 5px;
      font-weight: 600;
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
<?php if (!empty($errors)): ?>
  <div class="message error">
    <?php foreach ($errors as $err): ?>
      <p><?=htmlspecialchars($err)?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($_SESSION['message'])): ?>
  <div class="message success">
    <p><?=htmlspecialchars($_SESSION['message'])?></p>
  </div>
  <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<!-- Food & Beverages Grid -->
<div class="fnb-container" id="fnbItems">

  <!-- Add New Item Card -->
  <div class="fnb-card" id="addFnb" onclick="openModal()">
    <div class="add-fnb-card">+</div>
  </div>

  <!-- Product Cards -->
  <?php foreach ($products as $item): ?>
    <div class="fnb-card">
      <div class="fnb-info">
        <h3><?=htmlspecialchars($item['name'])?></h3>
        <p><strong>Category:</strong> <?=htmlspecialchars($item['category'])?></p>
        <p><strong>Price:</strong> $<?=number_format($item['price'], 2)?></p>
      </div>
      <div class="edit-icon" title="Edit Product" onclick="editProduct(<?=htmlspecialchars(json_encode($item))?>)">✎</div>
    </div>
  <?php endforeach; ?>

</div>

<!-- Modal for Adding / Editing Product -->
<div id="fnbModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <h2 id="modalTitle">Add Product</h2>
    <form id="fnbForm" method="POST" action="P6.php">
      <input type="hidden" id="editId" name="id" value="">
      <input type="text" id="name" name="name" placeholder="Product Name" required />
      <select id="category" name="category" required>
        <option value="" disabled selected>Select Category</option>
        <option value="Snacks">Snacks</option>
        <option value="Drinks">Drinks</option>
        <option value="Combos">Combos</option>
        <option value="Desserts">Desserts</option>
      </select>
      <input type="number" id="price" name="price" step="0.01" min="0" placeholder="Price in USD" required />
      <button type="submit" id="submitBtn">Add</button>
    </form>
  </div>
</div>

<script>
  // Open modal for adding new product
  function openModal() {
    document.getElementById('modalTitle').innerText = 'Add Product';
    document.getElementById('submitBtn').innerText = 'Add';
    document.getElementById('editId').value = '';
    document.getElementById('name').value = '';
    document.getElementById('category').value = '';
    document.getElementById('price').value = '';
    document.getElementById('fnbModal').style.display = 'block';
  }

  // Close modal
  function closeModal() {
    document.getElementById('fnbModal').style.display = 'none';
  }

  // Open modal with product data to edit (optional enhancement)
  function editProduct(product) {
    document.getElementById('modalTitle').innerText = 'Edit Product';
    document.getElementById('submitBtn').innerText = 'Update';
    document.getElementById('editId').value = product.id || '';
    document.getElementById('name').value = product.name || '';
    document.getElementById('category').value = product.category || '';
    document.getElementById('price').value = product.price || '';
    document.getElementById('fnbModal').style.display = 'block';
  }

  // Close modal if clicking outside modal content
  window.onclick = function(event) {
    const modal = document.getElementById('fnbModal');
    if (event.target === modal) {
      closeModal();
    }
  };
</script>
</main>

</body>
</html>