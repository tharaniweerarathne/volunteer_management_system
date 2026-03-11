<?php

session_start();

if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/MessageLogic.php";

$messageLogic = new MessageLogic($conn);
$inboxResult = $messageLogic->getInbox($_SESSION['userId'], 1, 1);

echo json_encode([
    'success' => true,
    'unreadCount' => $inboxResult['unreadCount']
]);
?>