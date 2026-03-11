<?php


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

// Get message
$result = $messageLogic->getMessage($messageId, $userId);

if (!$result['success']) {
    header("Location: inbox.php");
    exit();
}

$message = $result['data'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message</title>
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    background: linear-gradient(135deg, #fff5f0 0%, #ffe8d9 100%);
    min-height: 100vh;
    padding: 20px;
}

.container {
    max-width: 800px;
    margin: 20px auto;
    padding: 40px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(255, 107, 0, 0.08);
}

.message-header {
    background: linear-gradient(135deg, #fff5f0 0%, #ffe8d9 100%);
    padding: 30px;
    border-radius: 16px;
    margin-bottom: 25px;
    border: 2px solid #fff5f0;
}

.message-header h2 {
    font-size: 24px;
    color: #1a1a1a;
    font-weight: 700;
    margin-bottom: 20px;
    line-height: 1.4;
}

.message-header p {
    margin-bottom: 10px;
    font-size: 15px;
    color: #4a4a4a;
}

.message-header p:last-child {
    margin-bottom: 0;
}

.label {
    font-weight: 600;
    color: #ff6b00;
    margin-right: 8px;
}

.message-body {
    background: white;
    padding: 30px;
    border: 2px solid #fff5f0;
    border-radius: 16px;
    line-height: 1.8;
    font-size: 15px;
    color: #2a2a2a;
    min-height: 200px;
}

.button-group {
    margin-top: 30px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

button,
a.button {
    background: linear-gradient(135deg, #ff6b00 0%, #ff8533 100%);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 107, 0, 0.3);
}

button:hover,
a.button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 107, 0, 0.4);
    opacity: 1;
}

button:active,
a.button:active {
    transform: translateY(0);
}

button.delete,
a.button.delete {
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
    margin-left: 0;
}

button.delete:hover,
a.button.delete:hover {
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
}

/* Responsive design */
@media (max-width: 768px) {
    .container {
        padding: 20px;
        margin: 10px;
    }
    
    .message-header {
        padding: 20px;
    }
    
    .message-header h2 {
        font-size: 20px;
    }
    
    .message-body {
        padding: 20px;
    }
    
    .button-group {
        flex-direction: column;
    }
    
    button,
    a.button {
        width: 100%;
        text-align: center;
    }
}
    </style>
</head>
<body>
    <div class="container">
        <div class="message-header">
            <h2><?php echo htmlspecialchars($message['subject']); ?></h2>
            <p><span class="label">From:</span> <?php echo htmlspecialchars($message['senderName']); ?> (<?php echo htmlspecialchars($message['senderRole']); ?>)</p>
            <p><span class="label">To:</span> <?php echo htmlspecialchars($message['receiverName']); ?> (<?php echo htmlspecialchars($message['receiverRole']); ?>)</p>
            <p><span class="label">Date:</span> <?php echo date('F j, Y g:i A', strtotime($message['sentAt'])); ?></p>
        </div>
        
<div class="message-body">
    <?php 
    
    $allowed_tags = '<p><br><div><span><strong><em><b><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><table><tr><td><th>';
    echo strip_tags($message['message'], $allowed_tags);
    ?>
</div>
        
        <div class="button-group">
            <a href="sent_messages.php" class="button">Back</a>
            <a href="send_message.php?reply=<?php echo $messageId; ?>" class="button">Reply</a>
            <a href="delete_message.php?id=<?php echo $messageId; ?>" class="button delete" onclick="return confirm('Delete this message?')">Delete</a>
        </div>
    </div>
</body>
</html>