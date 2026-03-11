<?php
require_once __DIR__ . '/../data_access/db.php';
require_once __DIR__ . '/../business_logic/ContactMessageLogic.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $contactLogic = new ContactMessageLogic($conn);
    $result = $contactLogic->submitContactMessage($name, $email, $message);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['contact_result'] = $result;
    header("Location:index.php#contact");
    exit();
}
?>