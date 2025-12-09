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
            'createdBy' => $_SESSION['userId']
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


    // In your EventLogic class
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
    // Add debugging
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

    // Validate required fields
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
    // Set timezone (or ensure it's already set globally)
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
        
        // SMTP Configuration - UPDATE THESE!
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'infocontact256@gmail.com';  // Your Gmail
        $mail->Password   = 'ffvr keeu ztxj bwpa';     // App password (NOT regular password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Sender
        $mail->setFrom('noreply@volunteer.com', 'Volunteer Management');
        $mail->addAddress($to);
        
        // Content
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
    
    // Get user details
    global $conn;
    $userSql = "SELECT name, email FROM users WHERE userId = ?";
    $stmt = $conn->prepare($userSql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    // Get event details
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
    // Get user details
    global $conn;
    $userSql = "SELECT name, email FROM users WHERE userId = ?";
    $stmt = $conn->prepare($userSql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    // Get event details
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
    // Get user details
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
    
    // Check if already joined
    if ($registrationData->isAlreadyJoined($eventId, $userId)) {
        return ['success' => false, 'message' => 'Already registered for this event'];
    }
    
    // Check time conflicts
    $conflicts = $registrationData->checkTimeConflict($userId, $eventId);
    if (!empty($conflicts)) {
        // Send conflict email
        $this->sendConflictEmail($userId, $conflicts, 'Event Name');
        return ['success' => false, 'message' => 'Time conflict detected', 'conflicts' => $conflicts];
    }
    
    // Get event details
    $event = $registrationData->getEventDetails($eventId);
    if ($event['availableSlots'] <= 0) {
        return ['success' => false, 'message' => 'Event is full'];
    }
    
    // Insert registration
    if ($registrationData->insertRegistration($eventId, $userId)) {
        $registrationData->incrementJoinedCount($eventId);
        
        // Send confirmation email
        $this->sendRegistrationEmail($userId, $eventId);
        
        return ['success' => true, 'message' => 'Successfully registered'];
    }
    
    return ['success' => false, 'message' => 'Registration failed'];
}


public function sendEditConfirmationEmail($userId, $oldEventId, $newEventId) {
    // Get user details
    global $conn;
    $userSql = "SELECT name, email FROM users WHERE userId = ?";
    $stmt = $conn->prepare($userSql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    // Get event details
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
    // Get user details
    global $conn;
    $userSql = "SELECT name, email FROM users WHERE userId = ?";
    $stmt = $conn->prepare($userSql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    // Get event details
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
}

// Create a global instance for backward compatibility
$eventLogic = new EventLogic();
?>