<?php
session_start();
include('db_connect.php');

if (isset($_POST['submit'])) {
    $email = $_POST['email']; // Updated to match the form input name
    $password = $_POST['password'];

    // Corrected SQL query
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1"); 
    $stmt->bind_param("s", $email);  
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            
            // Correct redirection
            header("Location: home.html"); 
            exit; 
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "No user found with that email!";
    }

    $stmt->close();
}

$conn->close();
?>
