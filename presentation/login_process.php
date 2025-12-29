<?php
session_start();

require_once "../data_access/db.php";
require_once "../business_logic/UserLogic.php";

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$_SESSION['old_email'] = $email;

$userLogic = new UserLogic($conn);
$result = $userLogic->login($email, $password);

if ($result['success']) {
    $_SESSION['userId'] = $result['user']['userId'];
    $_SESSION['name'] = $result['user']['name'];
    $_SESSION['role'] = $result['user']['role'];

    // redirect based on role
    switch ($result['user']['role']) {
        case 'Admin':
            header("Location: ../presentation/admin_dashboard.php");
            break;
        case 'Coordinator':
            header("Location: ../presentation/coordinator_dashboard.php");
            break;
        case 'Organizer':
            header("Location: ../presentation/organizer_dashboard.php");
            break;
        default:
            header("Location: ../presentation/volunteer_dashboard.php");
    }
    exit;
} else {
    $_SESSION['login_error'] = $result['message'];
    header("Location: sign_in.php");
    exit;
}
?>
