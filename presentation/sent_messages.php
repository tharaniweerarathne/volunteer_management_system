<?php


require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/MessageLogic.php";

session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$userRole = $_SESSION['role'];
$userId = $_SESSION['userId'];
$messageLogic = new MessageLogic($conn);

// get current page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// get sent messages
$result = $messageLogic->getSentMessages($userId, $page);
$messages = $result['messages'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <title>Sent Messages</title>
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    margin: 0;
    padding: 0;
}

.navbar {
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}

.navbar .nav-link {
    border-radius: 8px;
    transition: all 0.3s ease;
}

.navbar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.bg-orange {
    background-color: #fd7e14 !important;
}

.container {
    max-width: 1000px;
    margin: 20px auto;
    padding: 40px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(255, 107, 0, 0.08);
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #fff5f0;
}

.header h2 {
    font-size: 28px;
    color: #1a1a1a;
    font-weight: 700;
}

.header div a {
    text-decoration: none;
    color: #ff6b00;
    font-weight: 500;
    transition: all 0.3s ease;
}

.header div a:hover {
    color: #ff8533;
}

button {
    background: linear-gradient(135deg, #ff6b00 0%, #ff8533 100%);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 107, 0, 0.3);
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 107, 0, 0.4);
}

button:active {
    transform: translateY(0);
}

.message-list {
    border: 2px solid #fff5f0;
    border-radius: 16px;
    overflow: hidden;
    background: white;
}

.message-item {
    padding: 20px;
    border-bottom: 1px solid #fff5f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.message-item:last-child {
    border-bottom: none;
}

.message-item:hover {
    background: #fff9f5;
    transform: translateX(4px);
}

.message-receiver {
    flex: 2;
    color: #4a4a4a;
    font-weight: 500;
    font-size: 15px;
}

.message-subject {
    flex: 3;
    color: #2a2a2a;
    font-size: 15px;
    padding: 0 20px;
}

.message-time {
    flex: 1;
    text-align: right;
    color: #999;
    font-size: 13px;
}

a {
    text-decoration: none;
    color: inherit;
}

.pagination {
    margin-top: 30px;
    text-align: center;
    display: flex;
    justify-content: center;
    gap: 8px;
}

.pagination a {
    display: inline-block;
    padding: 10px 18px;
    margin: 0;
    border: 2px solid #fff5f0;
    text-decoration: none;
    border-radius: 10px;
    color: #ff6b00;
    font-weight: 500;
    transition: all 0.3s ease;
    background: white;
}

.pagination a:hover {
    background: #fff9f5;
    border-color: #ff6b00;
    transform: translateY(-2px);
}

.pagination a.active {
    background: linear-gradient(135deg, #ff6b00 0%, #ff8533 100%);
    color: white;
    border-color: #ff6b00;
    box-shadow: 0 4px 12px rgba(255, 107, 0, 0.3);
}


.message-list .message-item[style*="text-align: center"] {
    color: #999;
    font-size: 16px;
    background: #fafafa;
}


@media (max-width: 768px) {
    .container {
        padding: 20px;
    }
    
    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .message-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .message-receiver,
    .message-subject,
    .message-time {
        width: 100%;
        text-align: left;
        padding: 0;
    }
}
    </style>
</head>
<body>
     <div class="d-flex">

<nav class="navbar navbar-dark bg-dark flex-column p-3" style="width: 250px; min-height: 100vh;">
    <a class="navbar-brand mb-4 d-flex align-items-center" href="#">
        <img src="../assets/images/logo.png" alt="Logo" style="width: 120px; height: 40px; object-fit: cover; border-radius: 8px; margin-right: 10px;">
    </a>
    <ul class="nav flex-column w-100">
<?php

if (isset($_SESSION['role'])) {
    $role = $_SESSION['role'];

    if ($role == 'Admin') {
        echo '<li class="nav-item mb-2">
                <a class="nav-link text-white" href="admin_dashboard.php">
                    <i class="ri-home-4-line"></i> Back to Dashboard
                </a>
              </li>';
    } elseif ($role == 'Coordinator') {
        echo '<li class="nav-item mb-2">
                <a class="nav-link text-white" href="coordinator_dashboard.php">
                    <i class="ri-home-4-line"></i> Back to Dashboard
                </a>
              </li>';
    } elseif ($role == 'Volunteer') {
        echo '<li class="nav-item mb-2">
                <a class="nav-link text-white" href="volunteer_dashboard.php">
                    <i class="ri-home-4-line"></i> Back to Dashboard
                </a>
              </li>';
    } elseif ($role == 'Organizer') {
        echo '<li class="nav-item mb-2">
                <a class="nav-link text-white" href="organizer_dashboard.php">
                    <i class="ri-home-4-line"></i> Back to Dashboard
                </a>
              </li>';
    }
}
?>

        <li class="nav-item mb-2">
            <a class="nav-link text-white" href="inbox.php">
                <i class="ri-inbox-line"></i> Inbox
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link active text-white" href="send_message.php">
                <i class="ri-send-plane-line"></i> Send Message
            </a>
        </li>

        <?php if ($userRole === 'Admin'): ?>
        <li class="nav-item mb-2">
            <a class="nav-link text-white" href="broadcast.php">
                <i class="ri-megaphone-line"></i> Broadcast
            </a>
        </li>
        <?php endif; ?>
        <li class="nav-item mb-2">
            <a class="nav-link bg-orange text-white" href="sent_messages.php">
                <i class="ri-mail-send-line"></i> Sent Messages
            </a>
        </li>
    </ul>
    <div class="mt-auto w-100">
        <hr class="text-white">
        <a class="nav-link text-white" href="logout.php" onclick="history.back(); return false;">
           <i class="ri-arrow-left-circle-line"></i> Back
        </a>
    </div>
</nav>


  <div class="flex-grow-1" style="background: linear-gradient(135deg, #fff5f0 0%, #ffe8d9 100%); padding: 20px;">


    <div class="container">
        <div class="header">
            <h2>Sent Messages</h2>
            <div>
                <a href="send_message.php"><button>Compose</button></a>
                <a href="inbox.php" style="margin-left: 10px;">Inbox</a>
            </div>
        </div>
        
        <div class="message-list">
            <?php if (empty($messages)): ?>
                <div class="message-item" style="text-align: center; padding: 40px;">
                    No sent messages
                </div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <a href="view_message.php?id=<?php echo $message['messageId']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="message-item">
                            <div class="message-receiver">
                                To: <?php echo htmlspecialchars($message['receiverName']); ?>
                            </div>
                            <div class="message-subject">
                                <?php echo htmlspecialchars($message['subject']); ?>
                            </div>
                            <div class="message-time">
                                <?php echo date('M d, H:i', strtotime($message['sentAt'])); ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($result['hasMore'] || $page > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>">Previous</a>
                <?php endif; ?>
                
                <a href="?page=<?php echo $page; ?>" class="active"><?php echo $page; ?></a>
                
                <?php if ($result['hasMore']): ?>
                    <a href="?page=<?php echo $page + 1; ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
                </div>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>