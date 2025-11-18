<?php
session_start();
require_once "../business_logic/UserLogic.php";

$conn = new mysqli("localhost", "root", "", "volunteer_management");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';


$_SESSION['old_email'] = $email;

$userLogic = new UserLogic($conn);
$result = $userLogic->login($email, $password);

if ($result['success']) {
    $_SESSION['userId'] = $result['user']['userId'];
    $_SESSION['name'] = $result['user']['name'];
    $_SESSION['role'] = $result['user']['role'];

    if ($result['user']['role'] === 'Admin') {
        header("Location: ../presentation/admin_dashboard.html");
    } elseif ($result['user']['role'] === 'Coordinator') {
        header("Location: ../presentation/coordinator_dashboard.html");
    } else {
        header("Location: ../presentation/volunteer_dashboard.html");
    }
    exit;
} else {
    $_SESSION['login_error'] = $result['message'];
    header("Location: sign_in.php");
    exit;
}

?>
