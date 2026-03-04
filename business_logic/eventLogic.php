<?php
require_once '../data_access/eventData.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

class eventLogic {
    private $eventData;
    
    public function __construct() {
        $this->eventData = new EventData();
    }
    
    // Get user role safely using OOP
    public function getUserRoleSafe() {
        if (isset($_SESSION['role'])) {
            return $_SESSION['role'];
        }
        
        if (isset($_SESSION['userId'])) {
            return $this->eventData->getUserRole($_SESSION['userId']);
        }
        
        return null;
    }
    
    // Handle image upload
    public function uploadImage($eventName) {
        if (!isset($_FILES['eventImage']) || $_FILES['eventImage']['error'] == UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['eventImage'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mime = mime_content_type($file['tmp_name']);
        
        if (!in_array($mime, $allowedTypes)) {
            return false;
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            return false;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . preg_replace('/[^a-z0-9]/i', '_', $eventName) . '.' . $extension;
        
        $uploadDir = '../assets/event_img/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $destination = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return 'assets/event_img/' . $filename;
        }
        
        return false;
    }
    
    // Create event
    public function handleCreateEvent() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $required = ['eventName', 'startDate', 'endDate', 'location'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                return ['success' => false, 'message' => "$field is required"];
            }
        }

        if (strtotime($_POST['endDate']) < strtotime($_POST['startDate'])) {
            return ['success' => false, 'message' => 'End date cannot be before start date'];
        }

        $imagePath = $this->uploadImage($_POST['eventName']);
        if ($imagePath === false) {
            return ['success' => false, 'message' => 'Invalid image file'];
        }

        // NEW: Determine createdBy based on role (for Organizer support)
        $createdBy = $_SESSION['userId'];
        if ($_SESSION['role'] === 'Admin' && isset($_POST['createdBy']) && !empty($_POST['createdBy'])) {
            $createdBy = $_POST['createdBy'];
        }

        $eventData = [
            'eventName' => $_POST['eventName'],
            'eventDescription' => $_POST['eventDescription'] ?? '',
            'category' => $_POST['category'] ?? '',
            'location' => $_POST['location'],
            'googleMapLink' => $_POST['googleMapLink'] ?? '',
            'startDate' => $_POST['startDate'],
            'endDate' => $_POST['endDate'],
            'startTime' => $_POST['startTime'] ?? '00:00',
            'endTime' => $_POST['endTime'] ?? '23:59',
            'maxVolunteers' => $_POST['maxVolunteers'] ?? 0,
            'requiredSkillId' => $_POST['requiredSkillId'] ?? null,
            'eventImage' => $imagePath,
            'createdBy' => $createdBy  // UPDATED: Use determined createdBy
        ];

        $eventId = $this->eventData->createEvent($eventData);
        
        if ($eventId) {
            // Assign coordinators if admin
            if ($_SESSION['role'] === 'Admin' && isset($_POST['coordinators'])) {
                $coordinatorIds = array_map('intval', $_POST['coordinators']);
                $assignResult = $this->eventData->assignCoordinators($eventId, $coordinatorIds);
                
                if (!$assignResult['success']) {
                    $conflictMsg = '';
                    foreach ($assignResult['conflicts'] as $conflict) {
                        $conflictMsg .= $conflict['coordinatorName'] . " has conflict with '{$conflict['eventName']}' ";
                    }
                    return ['success' => true, 'eventId' => $eventId, 'warning' => $conflictMsg];
                }
            }
            
            return ['success' => true, 'eventId' => $eventId];
        }
        
        return ['success' => false, 'message' => 'Failed to create event'];
    }

    // Get upcoming events
    public function getUpcomingEvents($filters = []) {
        try {
            $eventData = new EventData();
            $events = $eventData->getUpcomingEvents($filters);
            
            return [
                'success' => true,
                'events' => $events,
                'count' => count($events)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving upcoming events: ' . $e->getMessage(),
                'events' => [],
                'count' => 0
            ];
        }
    }
    
    // Update event
    public function handleUpdateEvent($eventId) {
        error_log("Update Event Called - ID: " . $eventId);
        error_log("POST Data: " . print_r($_POST, true));
        
        $event = $this->eventData->getEventById($eventId);
        if (!$event) {
            error_log("Event not found: " . $eventId);
            return ['success' => false, 'message' => 'Event not found'];
        }

        if (!$this->eventData->canUserEditEvent($eventId, $_SESSION['userId'])) {
            error_log("Permission denied for user: " . $_SESSION['userId']);
            return ['success' => false, 'message' => 'Permission denied'];
        }

        if (empty($_POST['eventName']) || empty($_POST['startDate']) || empty($_POST['endDate']) || empty($_POST['location'])) {
            return ['success' => false, 'message' => 'Required fields missing'];
        }

        $imagePath = $this->uploadImage($_POST['eventName']);
        if ($imagePath === false) {
            return ['success' => false, 'message' => 'Invalid image file'];
        }

        $eventData = [
            'eventName' => $_POST['eventName'],
            'eventDescription' => $_POST['eventDescription'] ?? '',
            'category' => $_POST['category'] ?? '',
            'location' => $_POST['location'],
            'googleMapLink' => $_POST['googleMapLink'] ?? '',
            'startDate' => $_POST['startDate'],
            'endDate' => $_POST['endDate'],
            'startTime' => $_POST['startTime'] ?? '00:00',
            'endTime' => $_POST['endTime'] ?? '23:59',
            'maxVolunteers' => $_POST['maxVolunteers'] ?? 0,
            'requiredSkillId' => !empty($_POST['requiredSkillId']) ? $_POST['requiredSkillId'] : null,
            'eventImage' => $imagePath !== null ? $imagePath : $event['eventImage']
        ];

        error_log("Updating with data: " . print_r($eventData, true));
        
        $success = $this->eventData->updateEvent($eventId, $eventData);
        
        error_log("Update result: " . ($success ? 'SUCCESS' : 'FAILED'));
        
        if ($success && $_SESSION['role'] === 'Admin' && isset($_POST['coordinators'])) {
            $coordinatorIds = array_map('intval', $_POST['coordinators']);
            $assignResult = $this->eventData->assignCoordinators($eventId, $coordinatorIds);
            
            if (!$assignResult['success']) {
                $conflictMsg = '';
                foreach ($assignResult['conflicts'] as $conflict) {
                    $conflictMsg .= $conflict['coordinatorName'] . " has conflict with '{$conflict['eventName']}' ";
                }
                return ['success' => true, 'warning' => $conflictMsg];
            }
        }
        
        return ['success' => $success, 'message' => $success ? 'Event updated' : 'Update failed'];
    }
    
    // NEW: Cancel event
    public function handleCancelEvent($eventId, $reason = '') {
        $event = $this->eventData->getEventById($eventId);
        if (!$event) {
            return ['success' => false, 'message' => 'Event not found'];
        }

        if (!$this->eventData->canUserCancelEvent($eventId, $_SESSION['userId'])) {
            return ['success' => false, 'message' => 'Permission denied'];
        }

        $success = $this->eventData->cancelEvent($eventId);
        
        if ($success) {
            // Send cancellation emails to all participants
            $participants = $this->eventData->getEventParticipants($eventId);
            foreach ($participants as $participant) {
                $this->sendEventCancellationEmail($participant['userId'], $event, $reason);
            }
        }
        
        return ['success' => $success, 'message' => $success ? 'Event cancelled successfully' : 'Failed to cancel event'];
    }
    
    // Delete event
    public function handleDeleteEvent($eventId) {
        $event = $this->eventData->getEventById($eventId);
        if (!$event) {
            return ['success' => false, 'message' => 'Event not found'];
        }

        if (!$this->eventData->canUserEditEvent($eventId, $_SESSION['userId'])) {
            return ['success' => false, 'message' => 'Permission denied'];
        }

        // Delete image file if exists
        if (!empty($event['eventImage']) && file_exists('../' . $event['eventImage'])) {
            unlink('../' . $event['eventImage']);
        }

        $success = $this->eventData->deleteEvent($eventId);
        
        return ['success' => $success, 'message' => $success ? 'Event deleted successfully' : 'Failed to delete event'];
    }
    
    // Check if event is over
    public function isEventOver($event) {
        date_default_timezone_set('Asia/Colombo');
        
        $endTime = !empty($event['endTime']) ? $event['endTime'] : '23:59:59';
        if (strlen($endTime) <= 5) {
            $endTime .= ':00';
        }
        
        $eventDateTime = $event['endDate'] . ' ' . $endTime;
        return strtotime($eventDateTime) < time();
    }

    // Email sending methods
    private function sendEmail($to, $subject, $body) {
        try {
            $mail = new PHPMailer(true);
            
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'infocontact256@gmail.com';  
            $mail->Password   = 'ffvr keeu ztxj bwpa';     
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            $mail->setFrom('noreply@volunteer.com', 'Unity Volunteers Trust');
            $mail->addAddress($to);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            
            return $mail->send();
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }

    public function sendRegistrationEmail($userId, $eventId) {
        require_once __DIR__ . '/../data_access/eventRegistrationData.php';
        $registrationData = new EventRegistrationData();
        
        global $conn;
        $userSql = "SELECT name, email FROM users WHERE userId = ?";
        $stmt = $conn->prepare($userSql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        $event = $registrationData->getEventDetails($eventId);
        
        if (!$user || !$event) return false;
        
        $subject = "Event Registration Confirmation";
        $body = "
        <h2>Event Registration Confirmed!</h2>
        <p>Dear " . htmlspecialchars($user['name']) . ",</p>
        <p>You have successfully registered for:</p>
        <h3>" . htmlspecialchars($event['eventName']) . "</h3>
        <p><strong>Date:</strong> " . date('F j, Y', strtotime($event['startDate'])) . "</p>
        <p><strong>Time:</strong> " . date('h:i A', strtotime($event['startTime'])) . " - " . 
                             date('h:i A', strtotime($event['endTime'])) . "</p>
        <p><strong>Location:</strong> " . htmlspecialchars($event['location']) . "</p>
        <p>Thank you for volunteering!</p>
        ";
        
        return $this->sendEmail($user['email'], $subject, $body);
    }

    public function sendCancellationEmail($userId, $eventId, $reason = null) {
        global $conn;
        $userSql = "SELECT name, email FROM users WHERE userId = ?";
        $stmt = $conn->prepare($userSql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        require_once __DIR__ . '/../data_access/eventRegistrationData.php';
        $registrationData = new EventRegistrationData();
        $event = $registrationData->getEventDetails($eventId);
        
        if (!$user || !$event) return false;
        
        $subject = "Event Registration Cancelled";
        $reasonText = $reason ? "<p><strong>Reason:</strong> " . htmlspecialchars($reason) . "</p>" : "";
        
        $body = "
        <h2>Registration Cancelled</h2>
        <p>Dear " . htmlspecialchars($user['name']) . ",</p>
        <p>Your registration has been cancelled for:</p>
        <h3>" . htmlspecialchars($event['eventName']) . "</h3>
        <p><strong>Date:</strong> " . date('F j, Y', strtotime($event['startDate'])) . "</p>
        " . $reasonText . "
        <p>We hope to see you in future events!</p>
        ";
        
        return $this->sendEmail($user['email'], $subject, $body);
    }

    public function sendConflictEmail($userId, $conflicts, $newEventName) {
        global $conn;
        $userSql = "SELECT name, email FROM users WHERE userId = ?";
        $stmt = $conn->prepare($userSql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if (!$user) return false;
        
        $subject = "Event Registration Conflict";
        $conflictList = "";
        foreach ($conflicts as $conflict) {
            $conflictList .= "<li>" . htmlspecialchars($conflict['eventName']) . 
                            " (" . date('M j, Y', strtotime($conflict['startDate'])) . ")</li>";
        }
        
        $body = "
        <h2>Registration Conflict Detected</h2>
        <p>Dear " . htmlspecialchars($user['name']) . ",</p>
        <p>Your registration for <strong>" . htmlspecialchars($newEventName) . "</strong> 
        conflicts with your existing events:</p>
        <ul>" . $conflictList . "</ul>
        <p>Please choose a different event or contact the event coordinator.</p>
        ";
        
        return $this->sendEmail($user['email'], $subject, $body);
    }

    // Simple join event function
    public function joinEvent($eventId, $userId) {
        require_once __DIR__ . '/../data_access/eventRegistrationData.php';
        $registrationData = new EventRegistrationData();
        
        if ($registrationData->isAlreadyJoined($eventId, $userId)) {
            return ['success' => false, 'message' => 'Already registered for this event'];
        }
        
        $conflicts = $registrationData->checkTimeConflict($userId, $eventId);
        if (!empty($conflicts)) {
            $this->sendConflictEmail($userId, $conflicts, 'Event Name');
            return ['success' => false, 'message' => 'Time conflict detected', 'conflicts' => $conflicts];
        }
        
        $event = $registrationData->getEventDetails($eventId);
        if ($event['availableSlots'] <= 0) {
            return ['success' => false, 'message' => 'Event is full'];
        }
        
        if ($registrationData->insertRegistration($eventId, $userId)) {
            $registrationData->incrementJoinedCount($eventId);
            $this->sendRegistrationEmail($userId, $eventId);
            
            return ['success' => true, 'message' => 'Successfully registered'];
        }
        
        return ['success' => false, 'message' => 'Registration failed'];
    }

    public function sendEditConfirmationEmail($userId, $oldEventId, $newEventId) {
        global $conn;
        $userSql = "SELECT name, email FROM users WHERE userId = ?";
        $stmt = $conn->prepare($userSql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        require_once __DIR__ . '/../data_access/eventRegistrationData.php';
        $registrationData = new EventRegistrationData();
        $oldEvent = $registrationData->getEventDetails($oldEventId);
        $newEvent = $registrationData->getEventDetails($newEventId);
        
        if (!$user || !$oldEvent || !$newEvent) return false;
        
        $subject = "Event Registration Changed";
        
        $body = "
        <h2>Event Registration Updated</h2>
        <p>Dear " . htmlspecialchars($user['name']) . ",</p>
        <p>Your event registration has been changed:</p>
        
        <h4>Old Event:</h4>
        <p><strong>" . htmlspecialchars($oldEvent['eventName']) . "</strong></p>
        <p><strong>Date:</strong> " . date('F j, Y', strtotime($oldEvent['startDate'])) . "</p>
        <p><strong>Time:</strong> " . date('h:i A', strtotime($oldEvent['startTime'])) . "</p>
        
        <h4>New Event:</h4>
        <p><strong>" . htmlspecialchars($newEvent['eventName']) . "</strong></p>
        <p><strong>Date:</strong> " . date('F j, Y', strtotime($newEvent['startDate'])) . "</p>
        <p><strong>Time:</strong> " . date('h:i A', strtotime($newEvent['startTime'])) . "</p>
        <p><strong>Location:</strong> " . htmlspecialchars($newEvent['location']) . "</p>
        
        <p>Thank you for volunteering!</p>
        ";
        
        return $this->sendEmail($user['email'], $subject, $body);
    }

    public function sendRejoinEmail($userId, $eventId) {
        global $conn;
        $userSql = "SELECT name, email FROM users WHERE userId = ?";
        $stmt = $conn->prepare($userSql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        require_once __DIR__ . '/../data_access/eventRegistrationData.php';
        $registrationData = new EventRegistrationData();
        $event = $registrationData->getEventDetails($eventId);
        
        if (!$user || !$event) return false;
        
        $subject = "Event Re-joined Successfully";
        
        $body = "
        <h2>Event Re-joined!</h2>
        <p>Dear " . htmlspecialchars($user['name']) . ",</p>
        <p>You have successfully re-joined the event:</p>
        <h3>" . htmlspecialchars($event['eventName']) . "</h3>
        <p><strong>Date:</strong> " . date('F j, Y', strtotime($event['startDate'])) . "</p>
        <p><strong>Time:</strong> " . date('h:i A', strtotime($event['startTime'])) . " - " . 
                             date('h:i A', strtotime($event['endTime'])) . "</p>
        <p><strong>Location:</strong> " . htmlspecialchars($event['location']) . "</p>
        <p>Welcome back! We're glad to have you volunteering again for this event.</p>
        ";
        
        return $this->sendEmail($user['email'], $subject, $body);
    }

    // NEW: Send event cancellation email (for cancelled events)
    public function sendEventCancellationEmail($userId, $event, $reason = '') {
        global $conn;
        
        $userSql = "SELECT name, email FROM users WHERE userId = ?";
        $stmt = $conn->prepare($userSql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if (!$user) return false;
        
        $subject = "❌ Event Cancelled: " . $event['eventName'];
        
        $reasonText = $reason ? "<p><strong>Reason:</strong> " . htmlspecialchars($reason) . "</p>" : "";
        
        $body = "
        <h2>Event Cancelled</h2>
        <p>Dear " . htmlspecialchars($user['name']) . ",</p>
        <p>We regret to inform you that the following event has been cancelled:</p>
        <h3>" . htmlspecialchars($event['eventName']) . "</h3>
        <p><strong>Date:</strong> " . date('F j, Y', strtotime($event['startDate'])) . "</p>
        <p><strong>Time:</strong> " . date('h:i A', strtotime($event['startTime'])) . " - " . 
                             date('h:i A', strtotime($event['endTime'])) . "</p>
        <p><strong>Location:</strong> " . htmlspecialchars($event['location']) . "</p>
        " . $reasonText . "
        <p>We apologize for any inconvenience this may cause. Please check our website for other upcoming events.</p>
        <p>Best regards,<br>Unity Volunteers Trust</p>
        ";
        
        // Also send internal message
        require_once __DIR__ . "/../data_access/MessageData.php";
        $messageData = new MessageData($conn);
        
        $adminSql = "SELECT userId FROM users WHERE role = 'Admin' LIMIT 1";
        $adminResult = $conn->query($adminSql);
        $admin = $adminResult->fetch_assoc();
        
        if ($admin) {
            $messageBody = "Event '" . $event['eventName'] . "' scheduled for " . 
                          date('F j, Y', strtotime($event['startDate'])) . " has been cancelled." .
                          ($reason ? "\nReason: " . $reason : "");
            
            $messageData->sendMessage($admin['userId'], $userId, $subject, $messageBody);
        }
        
        return $this->sendEmail($user['email'], $subject, $body);
    }

    // Simple method for all volunteer notifications
    public function notifyVolunteer($volunteerId, $eventId, $action, $reason = null) {
        require_once __DIR__ . "/../data_access/eventRegistrationData.php";
        $registrationData = new EventRegistrationData();
        $event = $registrationData->getEventDetails($eventId);
        
        if (!$event) return false;
        
        global $conn;
        $stmt = $conn->prepare("SELECT name FROM users WHERE userId = ?");
        $stmt->bind_param("i", $volunteerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $volunteer = $result->fetch_assoc();
        
        if (!$volunteer) return false;
        
        $adminSql = "SELECT userId FROM users WHERE role = 'Admin' LIMIT 1";
        $adminResult = $conn->query($adminSql);
        $admin = $adminResult->fetch_assoc();
        
        if (!$admin) return false;
        
        $adminId = $admin['userId'];
        
        $messages = [
            'join' => [
                'subject' => "✅ Event Registration Confirmed: " . $event['eventName'],
                'body' => "Hello " . $volunteer['name'] . ",\n\nYour registration has been confirmed for '" . $event['eventName'] . "' on " . date('F j, Y', strtotime($event['startDate'])) . ".\n\nBest regards,\nUnity Volunteers Trust"
            ],
            'rejoin' => [
                'subject' => "🔄 Welcome Back: " . $event['eventName'],
                'body' => "Hello " . $volunteer['name'] . ",\n\nWelcome back! You have re-joined '" . $event['eventName'] . "'.\n\nBest regards,\nUnity Volunteers Trust"
            ],
            'cancel' => [
                'subject' => "❌ Registration Cancelled: " . $event['eventName'],
                'body' => "Hello " . $volunteer['name'] . ",\n\nYour registration for '" . $event['eventName'] . "' has been cancelled." . ($reason ? "\nReason: " . $reason : "") . "\n\nBest regards,\nUnity Volunteers Trust"
            ]
        ];
        
        if (!isset($messages[$action])) return false;
        
        require_once __DIR__ . "/../data_access/MessageData.php";
        $messageData = new MessageData($conn);
        
        return $messageData->sendMessage($adminId, $volunteerId, $messages[$action]['subject'], $messages[$action]['body']);
    }

    // Send message when volunteer changes event registration
    public function notifyVolunteerEventChange($volunteerId, $oldEventId, $newEventId) {
        require_once __DIR__ . "/../data_access/eventRegistrationData.php";
        $registrationData = new EventRegistrationData();
        $oldEvent = $registrationData->getEventDetails($oldEventId);
        $newEvent = $registrationData->getEventDetails($newEventId);
        
        if (!$oldEvent || !$newEvent) return false;
        
        global $conn;
        $stmt = $conn->prepare("SELECT name FROM users WHERE userId = ?");
        $stmt->bind_param("i", $volunteerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $volunteer = $result->fetch_assoc();
        
        if (!$volunteer) return false;
        
        $adminSql = "SELECT userId FROM users WHERE role = 'Admin' LIMIT 1";
        $adminResult = $conn->query($adminSql);
        $admin = $adminResult->fetch_assoc();
        
        if (!$admin) return false;
        
        $adminId = $admin['userId'];
        
        $subject = "🔄 Event Registration Changed";
        
        $message = "Hello " . htmlspecialchars($volunteer['name']) . ",\n\n";
        $message .= "Your event registration has been updated:\n\n";
        $message .= "📅 FROM: " . $oldEvent['eventName'] . "\n";
        $message .= "   Date: " . date('F j, Y', strtotime($oldEvent['startDate'])) . "\n";
        $message .= "   Time: " . date('h:i A', strtotime($oldEvent['startTime'])) . "\n\n";
        $message .= "📅 TO: " . $newEvent['eventName'] . "\n";
        $message .= "   Date: " . date('F j, Y', strtotime($newEvent['startDate'])) . "\n";
        $message .= "   Time: " . date('h:i A', strtotime($newEvent['startTime'])) . "\n";
        $message .= "   Location: " . $newEvent['location'] . "\n\n";
        $message .= "Please review the new event details.\n\n";
        $message .= "Best regards,\nUnity Volunteers Trust";
        
        require_once __DIR__ . "/../data_access/MessageData.php";
        $messageData = new MessageData($conn);
        
        return $messageData->sendMessage($adminId, $volunteerId, $subject, $message);
    }

    

    // ai functions

    public function predictEventParticipation($eventId){
    require_once __DIR__ . "/PredictionService.php";

    $event = $this->eventData->getEventById($eventId);

    if (!$event) {
        return [
            'success' => false,
            'message' => 'Event not found'
        ];
    }

    $attendanceCount = $this->eventData->getEventAttendanceCount($eventId);

    // Parse the time to get hour AND minute
    $startTime = strtotime($event['startTime']);
    
    $featureData = [
        "eventId" => intval($event['eventId']),
        "category" => $event['category'],
        "location" => $event['location'],
        "startDate" => $event['startDate'],
        "startTime" => $event['startTime'],
        "maxVolunteers" => intval($event['maxVolunteers']),
        "requiredSkillId" => intval($event['requiredSkillId'] ?? 0),
        "attendance_count" => intval($attendanceCount),

        // Derived time features (matching your test file)
        "month" => intval(date('m', strtotime($event['startDate']))),
        "day" => intval(date('d', strtotime($event['startDate']))),
        "year" => intval(date('Y', strtotime($event['startDate']))),
        "day_of_week" => intval(date('w', strtotime($event['startDate']))),
        "hour" => intval(date('H', $startTime)),
        "minute" => intval(date('i', $startTime))  // ← ADD THIS LINE
    ];

    try {
        $predictionService = new PredictionService();
        $result = $predictionService->predictParticipation($featureData);

        // Debug logging
        error_log("Prediction features: " . json_encode($featureData));
        error_log("Prediction result: " . json_encode($result));

        return [
            'success' => true,
            'prediction' => $result['prediction'] ?? null
        ];

    } catch (Exception $e){
        error_log("Prediction error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}



public function getRecommendedEvents($userId){

    require_once "RecommendationService.php";

    $service = new RecommendationService();

    return $service->getRecommendedEventsForVolunteer($userId);
}

}

// Create a global instance for backward compatibility
$eventLogic = new EventLogic();
?>