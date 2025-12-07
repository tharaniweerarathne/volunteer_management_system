<?php
// presentation/delete_message.php

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/MessageLogic.php";

session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userId'];

if (!isset($_GET['id'])) {
    header("Location: inbox.php");
    exit();
}

$messageId = (int)$_GET['id'];
$messageLogic = new MessageLogic($conn);

// Delete the message
$result = $messageLogic->deleteMessage($messageId, $userId);

if ($result['success']) {
    $_SESSION['success'] = "Message deleted successfully";
} else {
    $_SESSION['error'] = $result['message'];
}

header("Location: inbox.php");
exit();
?>