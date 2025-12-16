<?php
require_once '../data_access/attendanceData.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class AttendanceLogic {
    private $attendanceData;
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->attendanceData = new AttendanceData();
        $this->conn = $conn;
    }
    
    // Check if user is coordinator
    public function isCoordinator($userId = null) {
        if (!$userId && isset($_SESSION['userId'])) {
            $userId = $_SESSION['userId'];
        }
        
        if (!$userId || !isset($_SESSION['role'])) {
            return false;
        }
        
        return $_SESSION['role'] === 'Coordinator';
    }
    
    // Get events for coordinator on specific date
    public function getCoordinatorEvents($date = null) {
        if (!$this->isCoordinator()) {
            return ['success' => false, 'message' => 'Access denied'];
        }
        
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        try {
            $events = $this->attendanceData->getCoordinatorEvents($_SESSION['userId'], $date);
            
            // Check event status
            foreach ($events as &$event) {
                $event['status'] = $this->getEventStatus($event);
            }
            
            return [
                'success' => true,
                'events' => $events,
                'date' => $date
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching events: ' . $e->getMessage()
            ];
        }
    }
    
    // Get event status
    private function getEventStatus($event) {
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        
        if ($currentDate > $event['endDate']) {
            return 'Completed';
        } elseif ($currentDate == $event['endDate'] && $currentTime > $event['endTime']) {
            return 'Completed';
        } elseif ($currentDate < $event['startDate']) {
            return 'Upcoming';
        } else {
            return 'Ongoing';
        }
    }
    
    // Get volunteers for event
// Get volunteers for event
public function getEventVolunteers($eventId, $search = '') {
    if (!$this->isCoordinator()) {
        return ['success' => false, 'message' => 'Access denied'];
    }
    
    // Check if coordinator is assigned to this event
    if (!$this->attendanceData->isCoordinatorAssigned($_SESSION['userId'], $eventId)) {
        return ['success' => false, 'message' => 'Not assigned to this event'];
    }
    
    try {
        $volunteers = $this->attendanceData->getEventVolunteers($eventId, $search);
        $summary = $this->attendanceData->getAttendanceSummary($eventId);
        
        return [
            'success' => true,
            'volunteers' => $volunteers,
            'summary' => $summary
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error fetching volunteers: ' . $e->getMessage()
        ];
    }
}
    
    // Mark attendance
    public function markAttendance($eventId, $attendances, $date = null) {
        if (!$this->isCoordinator()) {
            return ['success' => false, 'message' => 'Access denied'];
        }
        
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        // Check if coordinator is assigned
        if (!$this->attendanceData->isCoordinatorAssigned($_SESSION['userId'], $eventId)) {
            return ['success' => false, 'message' => 'Not assigned to this event'];
        }
        
        try {
            $success = $this->attendanceData->bulkMarkAttendance(
                $eventId, 
                $date, 
                $_SESSION['userId'], 
                $attendances
            );
            
            if ($success) {
                // Send notifications to volunteers using your MessageData
                $this->sendAttendanceNotifications($eventId, $attendances, $date);
                
                return [
                    'success' => true,
                    'message' => 'Attendance marked successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to mark attendance'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    // Send attendance notifications using your MessageData
    private function sendAttendanceNotifications($eventId, $attendances, $date) {
        // Get event details
        $eventSql = "SELECT eventName FROM events WHERE eventId = ?";
        $eventStmt = $this->conn->prepare($eventSql);
        $eventStmt->bind_param("i", $eventId);
        $eventStmt->execute();
        $event = $eventStmt->get_result()->fetch_assoc();
        
        if (!$event) return;
        
        // Load your MessageData
        require_once '../data_access/MessageData.php';
        $messageData = new MessageData($this->conn);
        
        // Get admin ID for sender
        $adminSql = "SELECT userId FROM users WHERE role = 'Admin' LIMIT 1";
        $adminResult = $this->conn->query($adminSql);
        $admin = $adminResult->fetch_assoc();
        $adminId = $admin ? $admin['userId'] : 1;
        
        foreach ($attendances as $userId => $data) {
            if ($data['status'] === 'Present') {
                // Send internal message
                $messageData->sendMessage(
                    $adminId,
                    $userId,
                    'Attendance Marked - Present',
                    "Your attendance has been marked as Present for '{$event['eventName']}' on " . date('F j, Y', strtotime($date))
                );
                
                // Send email notification
                $this->sendAttendanceEmail($userId, $eventId, $date, 'Present');
            } elseif ($data['status'] === 'Absent') {
                // Send message for absent
                $messageData->sendMessage(
                    $adminId,
                    $userId,
                    'Attendance Marked - Absent',
                    "Your attendance has been marked as Absent for '{$event['eventName']}' on " . date('F j, Y', strtotime($date))
                );
            }
        }
    }
    
    // Send attendance email
    private function sendAttendanceEmail($userId, $eventId, $date, $status) {
        try {
            // Get user email
            $userSql = "SELECT name, email FROM users WHERE userId = ?";
            $userStmt = $this->conn->prepare($userSql);
            $userStmt->bind_param("i", $userId);
            $userStmt->execute();
            $user = $userStmt->get_result()->fetch_assoc();
            
            // Get event details
            $eventSql = "SELECT eventName FROM events WHERE eventId = ?";
            $eventStmt = $this->conn->prepare($eventSql);
            $eventStmt->bind_param("i", $eventId);
            $eventStmt->execute();
            $event = $eventStmt->get_result()->fetch_assoc();
            
            if (!$user || !$event) return false;
            
            $mail = new PHPMailer(true);
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'infocontact256@gmail.com';
            $mail->Password   = 'ffvr keeu ztxj bwpa';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // Sender/Recipient
            $mail->setFrom('noreply@volunteer.com', 'Unity Volunteers Trust');
            $mail->addAddress($user['email'], $user['name']);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = "Attendance Marked: {$event['eventName']}";
            
            $body = "
            <h2>Attendance Update</h2>
            <p>Dear " . htmlspecialchars($user['name']) . ",</p>
            <p>Your attendance has been marked as <strong>{$status}</strong> for:</p>
            <h3>" . htmlspecialchars($event['eventName']) . "</h3>
            <p><strong>Date:</strong> " . date('F j, Y', strtotime($date)) . "</p>
            <p><strong>Status:</strong> {$status}</p>
            <p>Thank you for your participation!</p>
            <p>Best regards,<br>Unity Volunteers Trust</p>
            ";
            
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);
            
            return $mail->send();
            
        } catch (Exception $e) {
            error_log("Attendance email error: " . $e->getMessage());
            return false;
        }
    }
    
    // Auto-mark absent for unmarked volunteers after event ends
    public function autoMarkAbsent($eventId) {
        $date = date('Y-m-d');
        return $this->attendanceData->markAbsentForUnmarked($eventId, $date);
    }
    
    // Get attendance statistics for dashboard
    public function getAttendanceStats($coordinatorId = null) {
        if (!$coordinatorId && isset($_SESSION['userId'])) {
            $coordinatorId = $_SESSION['userId'];
        }
        
        $sql = "SELECT 
                COUNT(DISTINCT a.eventId) as totalEvents,
                COUNT(DISTINCT a.userId) as totalVolunteers,
                COUNT(CASE WHEN a.status = 'Present' THEN 1 END) as totalPresent,
                COUNT(CASE WHEN a.status = 'Absent' THEN 1 END) as totalAbsent
                FROM attendance a
                JOIN event_coordinators ec ON a.eventId = ec.eventId";
        
        if ($coordinatorId) {
            $sql .= " WHERE ec.coordinatorId = ?";
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if ($coordinatorId) {
            $stmt->bind_param("i", $coordinatorId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return [
            'success' => true,
            'stats' => $result
        ];
    }
}
?>