<?php
// presentation/broadcast.php

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/MessageLogic.php";

session_start();

if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userId'];
$userRole = $_SESSION['role'];

$messageLogic = new MessageLogic($conn);

// Get all volunteers for broadcast
$result = $messageLogic->getBroadcastRecipients($userId, $userRole);
$volunteers = $result['recipients'];
$volunteerCount = count($volunteers);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Get all volunteer IDs
    $receiverIds = array_column($volunteers, 'userId');
    
    $result = $messageLogic->sendMessage($userId, $userRole, $receiverIds, $subject, $message);
    
    if ($result['success']) {
        $success = "Message broadcasted to $volunteerCount volunteers successfully!";
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <title>Broadcast Message</title>
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
    max-width: 800px;
    margin: 20px auto;
    padding: 40px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(255, 107, 0, 0.08);
}

.container h2 {
    font-size: 28px;
    color: #1a1a1a;
    font-weight: 700;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #fff5f0;
}

.form-group {
    margin-bottom: 25px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2a2a2a;
    font-size: 14px;
}

input,
textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #fff5f0;
    border-radius: 12px;
    font-family: inherit;
    font-size: 15px;
    transition: all 0.3s ease;
    background: white;
}

input:focus,
textarea:focus {
    outline: none;
    border-color: #ff6b00;
    box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.1);
}

textarea {
    height: 250px;
    resize: vertical;
    min-height: 200px;
}

textarea::placeholder {
    color: #999;
}

.error {
    color: #dc2626;
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    border-left: 4px solid #dc2626;
    font-weight: 500;
}

.success {
    color: #059669;
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    border-left: 4px solid #059669;
    font-weight: 500;
}

.info-box {
    background: linear-gradient(135deg, #fff4e6 0%, #ffe8cc 100%);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    border-left: 4px solid #ff6b00;
    font-size: 15px;
    color: #4a4a4a;
}

.info-box strong {
    color: #ff6b00;
}

button {
    background: linear-gradient(135deg, #ff6b00 0%, #ff8533 100%);
    color: white;
    padding: 14px 32px;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
    font-size: 15px;
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

form a {
    color: #ff6b00;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-block;
    margin-left: 15px;
}

form a:hover {
    color: #ff8533;
    text-decoration: underline;
}

/* Responsive design */
@media (max-width: 768px) {
    .container {
        padding: 20px;
        margin: 10px;
    }
    
    .container h2 {
        font-size: 24px;
    }
    
    button {
        width: 100%;
        margin-bottom: 10px;
    }
    
    form a {
        display: block;
        text-align: center;
        margin-left: 0;
        margin-top: 10px;
    }
    
    .info-box {
        padding: 15px;
        font-size: 14px;
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
// Assuming you have the role stored in session, e.g., $_SESSION['role']
if (isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    
    if ($role == 'Admin') {
        echo '<li class="nav-item mb-2">
                <a class="nav-link text-white" href="admin_dashboard.php">
                    <i class="ri-home-4-line"></i> Back Dashboard
                </a>
              </li>';
    } elseif ($role == 'Coordinator') {
        echo '<li class="nav-item mb-2">
                <a class="nav-link text-white" href="coordinator_dashboard.php">
                    <i class="ri-home-4-line"></i> Back Dashboard
                </a>
              </li>';
    } elseif ($role == 'Volunteer') {
        echo '<li class="nav-item mb-2">
                <a class="nav-link text-white" href="volunteer_dashboard.php">
                    <i class="ri-home-4-line"></i> Back Dashboard
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
            <a class="nav-link bg-orange text-white" href="broadcast.php">
                <i class="ri-megaphone-line"></i> Broadcast
            </a>
        </li>
        <?php endif; ?>
        <li class="nav-item mb-2">
            <a class="nav-link text-white" href="sent_messages.php">
                <i class="ri-mail-open-line"></i> Sent Messages
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

<!--remove this-->
  <div class="flex-grow-1" style="background: linear-gradient(135deg, #fff5f0 0%, #ffe8d9 100%); padding: 20px;">
    <div class="container">
        <h2>📢 Broadcast to All Volunteers</h2>
        
        <div class="info-box">
            <strong>Note:</strong> This message will be sent to <strong><?php echo $volunteerCount; ?></strong> volunteers.
            Each volunteer will receive this as an individual message.
        </div>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject" required maxlength="255">
            </div>
            
            <div class="form-group">
                <label for="message">Message:</label>
                <textarea id="message" name="message" required placeholder="Type your broadcast message here..."></textarea>
            </div>
            
            <button type="submit">📢 Broadcast to All Volunteers</button>
            <a href="send_message.php" style="margin-left: 10px;">Back to Normal Message</a>
        </form>
    </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> 
</body>
</html>