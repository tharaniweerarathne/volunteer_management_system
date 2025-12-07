<?php
// presentation/send_message.php

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/MessageLogic.php";

session_start();

if (!isset($_SESSION['userId']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userId'];
$userRole = $_SESSION['role'];

$messageLogic = new MessageLogic($conn);

// Get available recipients
$recipientsResult = $messageLogic->getAvailableRecipients($userId, $userRole);
$availableUsers = $recipientsResult['success'] ? $recipientsResult['users'] : [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $receiverIds = $_POST['receiverIds'] ?? [];
    
    $result = $messageLogic->sendMessage($userId, $userRole, $receiverIds, $subject, $message);
    
    if ($result['success']) {
        $success = "Message sent successfully!";
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
    <link rel="stylesheet" href="../assets/css/internal_message.css">
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <title>Send Message</title>
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
textarea,
select {
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
textarea:focus,
select:focus {
    outline: none;
    border-color: #ff6b00;
    box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.1);
}

textarea {
    height: 200px;
    resize: vertical;
    min-height: 150px;
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

.recipient-list {
    max-height: 200px;
    overflow-y: auto;
    border: 2px solid #fff5f0;
    padding: 16px;
    border-radius: 12px;
    background: #fafafa;
}

.recipient-list::-webkit-scrollbar {
    width: 8px;
}

.recipient-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.recipient-list::-webkit-scrollbar-thumb {
    background: #ff6b00;
    border-radius: 10px;
}

.recipient-list::-webkit-scrollbar-thumb:hover {
    background: #ff8533;
}

.recipient-item {
    margin-bottom: 10px;
    padding: 8px;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.recipient-item:hover {
    background: white;
}

.recipient-item label {
    display: flex;
    align-items: center;
    cursor: pointer;
    margin-bottom: 0;
    font-weight: 500;
    color: #4a4a4a;
}

.recipient-item input[type="checkbox"] {
    width: auto;
    margin-right: 10px;
    cursor: pointer;
    width: 18px;
    height: 18px;
    accent-color: #ff6b00;
}

.form-group p {
    margin-top: 12px;
    font-size: 14px;
}

.form-group p a {
    color: #ff6b00;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.form-group p a:hover {
    color: #ff8533;
    text-decoration: underline;
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

.bg-orange {
    background-color: #fd7e14 !important;
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
            <a class="nav-link active bg-orange text-white" href="send_message.php">
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
        <h2>Send Message</h2>
        
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
                <label for="receiverIds">Recipients:</label>
                <div class="recipient-list">
                    <?php foreach ($availableUsers as $user): ?>
                        <div class="recipient-item">
                            <label>
                                <input type="checkbox" name="receiverIds[]" value="<?php echo $user['userId']; ?>">
                                <?php echo htmlspecialchars($user['name']); ?> 
                                (<?php echo htmlspecialchars($user['role']); ?>)
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($userRole === 'Admin'): ?>
                    <p><a href="broadcast.php">Send to all volunteers</a></p>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="message">Message:</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            
            <button type="submit">Send Message</button>
            <a href="inbox.php" style="margin-left: 10px;">Back to Inbox</a>
        </form>
    </div>
                </div>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>