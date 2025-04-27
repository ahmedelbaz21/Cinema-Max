<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $dob = $_POST["dob"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];

    // Check if passwords match
    if ($password !== $confirmPassword) {
        echo "Passwords do not match.";
        exit();
    }
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);


    // Check if email already exists
    $check_sql = "SELECT * FROM users WHERE email='$email'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo "This email is already registered.";
    } else {
        // Insert new user into the database
        $sql = "INSERT INTO users (first_name, last_name, email, phone, dob, password) 
                VALUES ('$firstName', '$lastName', '$email', '$phone', '$dob', '$hashedPassword')";
        
        if ($conn->query($sql) === TRUE) {
            echo "Registration successful! Redirecting to login...";
            header("refresh:2; url= login.html"); // Redirect to login.html after 2 seconds
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>
