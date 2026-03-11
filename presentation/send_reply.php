<?php
session_start();
require_once __DIR__ . '/../data_access/db.php';
require_once __DIR__ . '/../business_logic/ContactMessageLogic.php';


if (!isset($_SESSION['userId']) || !in_array($_SESSION['role'], ['Admin', 'Coordinator'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $messageId = intval($_POST['messageId'] ?? 0);
    $replyMessage = trim($_POST['replyMessage'] ?? '');
    $repliedBy = $_SESSION['userId'];

    if ($messageId && !empty($replyMessage)) {
        $contactLogic = new ContactMessageLogic($conn);
        $result = $contactLogic->sendReply($messageId, $repliedBy, $replyMessage);
        
        $_SESSION['reply_result'] = $result;
    } else {
        $_SESSION['reply_result'] = ["success" => false, "message" => "Invalid request."];
    }

    header('Location: view_messages.php');
    exit();
}
?>