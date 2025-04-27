<?php
session_start();
include('db_connect.php');

if (isset($_POST['submit'])) {
    $loginInput = $_POST['login_input']; 
    $password = $_POST['password'];

   
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR phone = ? LIMIT 1");
    $stmt->bind_param("ss", $loginInput, $loginInput);  
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['payment_method'] = $user['payment_method']; 

            header("Location: home.php");
            exit;
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "No user found with that email or phone!";
    }

    $stmt->close();
}

$conn->close();
?>
