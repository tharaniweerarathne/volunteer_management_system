<?php
session_start();
require_once __DIR__ . "/../business_logic/UserLogic.php";

// Connect to DB
$conn = new mysqli("localhost", "root", "", "volunteer_management");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get form data
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Input validation
if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = "Email and password are required";
    header("Location: sign_in.php");
    exit;
}

// Use business logic
$userLogic = new UserLogic($conn);
$result = $userLogic->login($email, $password);

if ($result['success']) {
    // Clear any old error messages
    unset($_SESSION['login_error'], $_SESSION['old_email'], $_SESSION['old_password']);
    
    // Set session variables
    $_SESSION['userId'] = $result['user']['userId'];
    $_SESSION['name'] = $result['user']['name'];
    $_SESSION['role'] = $result['user']['role'];

    // Role-based redirect
    if ($result['user']['role'] === 'Admin') {
        header("Location: admin_dashboard.html");
    } elseif ($result['user']['role'] === 'Coordinator') {
        header("Location: coordinator_dashboard.html");
    } else {
        header("Location: volunteer_dashboard.html");
    }
    exit;
} else {
    $_SESSION['login_error'] = $result['message'];
    $_SESSION['old_email'] = $email;
    header("Location: sign_in.php");
    exit;
}

$conn->close();
?>