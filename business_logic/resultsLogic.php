<?php
// resultsLogic.php ---> business_logic layer (UPDATED WITH MULTIPLE IMAGES)
require_once '../data_access/resultsData.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ResultsLogic {
    private $resultsData;
    
    public function __construct() {
        $this->resultsData = new ResultsData();
    }
    
    // Handle multiple image uploads for results
    public function uploadResultImages($resultTitle) {
        $uploadedImages = [];
        
        // Handle main image (resultImage)
        if (isset($_FILES['resultImage']) && $_FILES['resultImage']['error'] == UPLOAD_ERR_OK) {
            $mainImage = $this->uploadSingleImage($_FILES['resultImage'], $resultTitle, 'main');
            if ($mainImage) {
                $uploadedImages['resultImage'] = $mainImage;
            }
        }
        
        // Handle additional images (resultImage2 to resultImage5)
        for ($i = 2; $i <= 5; $i++) {
            $fieldName = 'resultImage' . $i;
            if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] == UPLOAD_ERR_OK) {
                $image = $this->uploadSingleImage($_FILES[$fieldName], $resultTitle, $i);
                if ($image) {
                    $uploadedImages[$fieldName] = $image;
                }
            }
        }
        
        return $uploadedImages;
    }
    
    // Helper function to upload a single image
    private function uploadSingleImage($file, $resultTitle, $imageNumber) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mime = mime_content_type($file['tmp_name']);
        
        if (!in_array($mime, $allowedTypes)) {
            return false;
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
            return false;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_result_' . preg_replace('/[^a-z0-9]/i', '_', $resultTitle) . 
                    '_' . $imageNumber . '.' . $extension;
        
        $uploadDir = '../assets/result_img/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $destination = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return 'assets/result_img/' . $filename;
        }
        
        return false;
    }
    
    // Delete old images when updating
    private function deleteOldImages($resultId, $keepImages = []) {
        $result = $this->resultsData->getResultById($resultId);
        if (!$result) {
            return false;
        }
        
        $imageFields = ['resultImage', 'resultImage2', 'resultImage3', 'resultImage4', 'resultImage5'];
        
        foreach ($imageFields as $field) {
            $oldImage = $result[$field] ?? null;
            
            // Check if this image should be kept
            $shouldKeep = false;
            foreach ($keepImages as $newField => $newImage) {
                if ($field === $newField) {
                    $shouldKeep = true;
                    break;
                }
            }
            
            // Delete old image if it exists and not being kept
            if ($oldImage && !$shouldKeep && file_exists('../' . $oldImage)) {
                unlink('../' . $oldImage);
            }
        }
        
        return true;
    }
    
    // Create new result with multiple images
    public function handleCreateResult() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $required = ['resultTitle', 'eventId', 'resultDate', 'description'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                return ['success' => false, 'message' => "$field is required"];
            }
        }

        // Validate date
        if (strtotime($_POST['resultDate']) > strtotime(date('Y-m-d'))) {
            return ['success' => false, 'message' => 'Result date cannot be in the future'];
        }

        // Upload multiple images
        $uploadedImages = $this->uploadResultImages($_POST['resultTitle']);
        if (empty($uploadedImages)) {
            // No images uploaded, but that's okay
            $uploadedImages = [];
        }

        // Determine organizer ID based on user role
        $organizerId = $this->determineOrganizerId();

        if (!$organizerId) {
            return ['success' => false, 'message' => 'Organizer selection is required'];
        }

        $resultData = [
            'eventId' => $_POST['eventId'],
            'organizerId' => $organizerId,
            'resultTitle' => $_POST['resultTitle'],
            'description' => $_POST['description'],
            'resultDate' => $_POST['resultDate'],
            'skillId' => $_POST['skillId'] ?? null,
            'addedBy' => $_SESSION['userId']
        ];

        // Add uploaded images to data
        foreach ($uploadedImages as $field => $imagePath) {
            $resultData[$field] = $imagePath;
        }

        $resultId = $this->resultsData->createResult($resultData);
        
        if ($resultId) {
            // Send notification to admin
            $this->sendNewResultNotification($resultId);
            
            return ['success' => true, 'message' => 'Result created successfully', 'resultId' => $resultId];
        }
        
        return ['success' => false, 'message' => 'Failed to create result'];
    }
    
    // Update result with multiple images
    public function handleUpdateResult($resultId) {
        $result = $this->resultsData->getResultById($resultId);
        if (!$result) {
            return ['success' => false, 'message' => 'Result not found'];
        }

        // Check permission
        if (!$this->resultsData->canUserModifyResult($resultId, $_SESSION['userId'])) {
            return ['success' => false, 'message' => 'Permission denied'];
        }

        $required = ['resultTitle', 'eventId', 'resultDate', 'description'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                return ['success' => false, 'message' => "$field is required"];
            }
        }

        // Upload new images
        $uploadedImages = $this->uploadResultImages($_POST['resultTitle']);
        
        // Get existing images that should be kept
        $keepImages = [];
        for ($i = 1; $i <= 5; $i++) {
            $fieldName = $i == 1 ? 'resultImage' : 'resultImage' . $i;
            $keepField = 'keep_' . $fieldName;
            
            // If user checked "keep" checkbox for this image
            if (isset($_POST[$keepField]) && $_POST[$keepField] == '1' && !empty($result[$fieldName])) {
                $keepImages[$fieldName] = $result[$fieldName];
            }
        }

        // Delete old images that are not being kept
        $this->deleteOldImages($resultId, array_merge($keepImages, $uploadedImages));

        // Determine organizer ID for update
        $organizerId = $this->determineOrganizerId();
        if (!$organizerId) {
            $organizerId = $result['organizerId']; // Keep existing organizer
        }

        $resultData = [
            'eventId' => $_POST['eventId'],
            'organizerId' => $organizerId,
            'resultTitle' => $_POST['resultTitle'],
            'description' => $_POST['description'],
            'resultDate' => $_POST['resultDate'],
            'skillId' => $_POST['skillId'] ?? null
        ];

        // Merge kept images and new uploaded images
        $allImages = array_merge($keepImages, $uploadedImages);
        foreach ($allImages as $field => $imagePath) {
            $resultData[$field] = $imagePath;
        }

        // Ensure all image fields are set (even if null)
        for ($i = 1; $i <= 5; $i++) {
            $fieldName = $i == 1 ? 'resultImage' : 'resultImage' . $i;
            if (!isset($resultData[$fieldName])) {
                $resultData[$fieldName] = null;
            }
        }

        $success = $this->resultsData->updateResult($resultId, $resultData);
        
        if ($success && $result['approvalStatus'] == 'Approved') {
            // If approved result was edited, reset to pending for re-approval
            $this->resultsData->rejectResult($resultId, $_SESSION['userId'], 'Edited after approval - requires re-approval');
        }
        
        return ['success' => $success, 'message' => $success ? 'Result updated successfully' : 'Update failed'];
    }
    
    // Determine organizer ID based on user role
    private function determineOrganizerId() {
        $role = $_SESSION['role'] ?? null;
        
        // If admin and organizer is specified, use that
        if ($role === 'Admin' && !empty($_POST['organizerId'])) {
            return $_POST['organizerId'];
        }
        
        // If user is an organizer, use their own ID
        if ($role === 'Organizer') {
            return $_SESSION['userId'];
        }
        
        // If user is a coordinator and organizer is specified
        if ($role === 'Coordinator' && !empty($_POST['organizerId'])) {
            return $_POST['organizerId'];
        }
        
        // For coordinator without organizer specified, check if they're also an organizer
        if ($role === 'Coordinator') {
            // Check if this coordinator is also registered as an organizer
            $userInfo = $this->getUserById($_SESSION['userId']);
            if ($userInfo && $userInfo['role'] === 'Organizer') {
                return $_SESSION['userId'];
            }
        }
        
        return null;
    }
    
    // Rest of the methods remain the same, just updating the getResultById method
    public function getResultById($resultId) {
        $result = $this->resultsData->getResultById($resultId);
        
        if ($result) {
            // Get all images as an array
            $images = [];
            for ($i = 1; $i <= 5; $i++) {
                $fieldName = $i == 1 ? 'resultImage' : 'resultImage' . $i;
                if (!empty($result[$fieldName])) {
                    $images[] = $result[$fieldName];
                }
            }
            $result['allImages'] = $images;
            
            return [
                'success' => true,
                'result' => $result
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Result not found'
        ];
    }
    
    // Get user by ID
    public function getUserById($userId) {
        global $conn;
        
        if (!$conn) {
            require_once '../data_access/db.php';
            global $conn;
        }
        
        $sql = "SELECT userId, name, email, role FROM users WHERE userId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Get all organizers for dropdown
    public function getAllOrganizers() {
        try {
            $organizers = $this->resultsData->getAllOrganizers();
            
            return [
                'success' => true,
                'organizers' => $organizers
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving organizers: ' . $e->getMessage(),
                'organizers' => []
            ];
        }
    }
    
    // Get organizers for a specific event
    public function getEventOrganizers($eventId) {
        try {
            $organizers = $this->resultsData->getEventOrganizers($eventId);
            
            return [
                'success' => true,
                'organizers' => $organizers
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving event organizers: ' . $e->getMessage(),
                'organizers' => []
            ];
        }
    }
    
    // Get events with organizers for dropdown
    public function getEventsWithOrganizers() {
        try {
            $events = $this->resultsData->getEventsWithOrganizers();
            
            return [
                'success' => true,
                'events' => $events
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving events: ' . $e->getMessage(),
                'events' => []
            ];
        }
    }
    
    // Approve result (admin only)
    public function handleApproveResult($resultId, $notes = '') {
        if ($_SESSION['role'] !== 'Admin') {
            return ['success' => false, 'message' => 'Permission denied'];
        }

        $result = $this->resultsData->getResultById($resultId);
        if (!$result) {
            return ['success' => false, 'message' => 'Result not found'];
        }

        $success = $this->resultsData->approveResult($resultId, $_SESSION['userId'], $notes);
        
        if ($success) {
            // Send notification to the user who added the result
            $this->sendResultApprovalEmail($result['addedById'], $resultId, 'approved', $notes);
            
            // Also notify the organizer if different from submitter
            if ($result['organizerId'] && $result['organizerId'] != $result['addedById']) {
                $this->sendResultApprovalEmail($result['organizerId'], $resultId, 'approved', $notes, 'organizer');
            }
        }
        
        return ['success' => $success, 'message' => $success ? 'Result approved successfully' : 'Failed to approve'];
    }
    
    // Reject result (admin only)
    public function handleRejectResult($resultId, $notes = '') {
        if ($_SESSION['role'] !== 'Admin') {
            return ['success' => false, 'message' => 'Permission denied'];
        }

        $result = $this->resultsData->getResultById($resultId);
        if (!$result) {
            return ['success' => false, 'message' => 'Result not found'];
        }

        $success = $this->resultsData->rejectResult($resultId, $_SESSION['userId'], $notes);
        
        if ($success) {
            // Send notification to the user who added the result
            $this->sendResultApprovalEmail($result['addedById'], $resultId, 'rejected', $notes);
            
            // Also notify the organizer if different from submitter
            if ($result['organizerId'] && $result['organizerId'] != $result['addedById']) {
                $this->sendResultApprovalEmail($result['organizerId'], $resultId, 'rejected', $notes, 'organizer');
            }
        }
        
        return ['success' => $success, 'message' => $success ? 'Result rejected' : 'Failed to reject'];
    }
    
    // Delete result
    public function handleDeleteResult($resultId) {
        $result = $this->resultsData->getResultById($resultId);
        if (!$result) {
            return ['success' => false, 'message' => 'Result not found'];
        }

        // Check permission
        if (!$this->resultsData->canUserModifyResult($resultId, $_SESSION['userId'])) {
            return ['success' => false, 'message' => 'Permission denied'];
        }

        $success = $this->resultsData->deleteResult($resultId);
        
        return ['success' => $success, 'message' => $success ? 'Result deleted successfully' : 'Failed to delete'];
    }
    
    // Get results with filters
    public function getResults($filters = []) {
        try {
            // Add user filter for non-admin users
            if ($_SESSION['role'] !== 'Admin') {
                $filters['addedBy'] = $_SESSION['userId'];
            }
            
            $results = $this->resultsData->getAllResults($filters);
            
            return [
                'success' => true,
                'results' => $results,
                'count' => count($results)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving results: ' . $e->getMessage(),
                'results' => [],
                'count' => 0
            ];
        }
    }
    
    // Get approved results for public display
    public function getPublicResults($limit = 10, $filters = []) {
        try {
            $results = $this->resultsData->getApprovedResults($limit, $filters);
            
            return [
                'success' => true,
                'results' => $results,
                'count' => count($results)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving results: ' . $e->getMessage(),
                'results' => [],
                'count' => 0
            ];
        }
    }
    
    // Get statistics
    public function getStatistics() {
        try {
            $userId = ($_SESSION['role'] !== 'Admin') ? $_SESSION['userId'] : null;
            $stats = $this->resultsData->getResultsStatistics($userId);
            
            return [
                'success' => true,
                'stats' => $stats
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving statistics: ' . $e->getMessage(),
                'stats' => ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0]
            ];
        }
    }
    
    // Get events for dropdown with organizer context
    public function getEventsForDropdown() {
        try {
            $userId = ($_SESSION['role'] !== 'Admin') ? $_SESSION['userId'] : null;
            $events = $this->resultsData->getEventsForResultSubmission($userId);
            
            return [
                'success' => true,
                'events' => $events
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving events: ' . $e->getMessage(),
                'events' => []
            ];
        }
    }
    
    // Get skills for dropdown
    public function getAllSkills() {
        try {
            require_once '../data_access/eventData.php';
            $eventData = new EventData();
            $skills = $eventData->getAllSkills();
            
            return [
                'success' => true,
                'skills' => $skills
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving skills: ' . $e->getMessage(),
                'skills' => []
            ];
        }
    }
    
    // Email sending methods (remain the same)
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
    
    // Send notification to admin about new result
    private function sendNewResultNotification($resultId) {
        global $conn;
        
        $result = $this->resultsData->getResultById($resultId);
        if (!$result) return false;
        
        // Get admin emails
        $adminSql = "SELECT email, name FROM users WHERE role = 'Admin'";
        $adminResult = $conn->query($adminSql);
        
        $subject = "📋 New Event Result Submitted for Review";
        
        while ($admin = $adminResult->fetch_assoc()) {
            $body = "
            <h2>New Event Result Submitted</h2>
            <p>Dear " . htmlspecialchars($admin['name']) . ",</p>
            <p>A new event result has been submitted and requires your review:</p>
            
            <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                <h3>" . htmlspecialchars($result['resultTitle']) . "</h3>
                <p><strong>Event:</strong> " . htmlspecialchars($result['eventName']) . "</p>
                <p><strong>Organizer:</strong> " . htmlspecialchars($result['organizerName'] ?? 'Not specified') . "</p>
                <p><strong>Submitted by:</strong> " . htmlspecialchars($result['addedByName']) . " (" . htmlspecialchars($result['addedByRole']) . ")</p>
                <p><strong>Date:</strong> " . date('F j, Y', strtotime($result['resultDate'])) . "</p>
                <p><strong>Description:</strong> " . nl2br(htmlspecialchars(substr($result['description'], 0, 200))) . "...</p>
            </div>
            
            <p>Please review and approve or reject this result in the admin panel.</p>
            
            <a href='http://yourdomain.com/admin/results_management.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;'>
                Review Now
            </a>
            
            <p>Best regards,<br>Unity Volunteers Trust</p>
            ";
            
            $this->sendEmail($admin['email'], $subject, $body);
        }
        
        return true;
    }
    
    // Send result approval email
    private function sendResultApprovalEmail($userId, $resultId, $status, $notes = '', $recipientType = 'submitter') {
        global $conn;
        
        $userSql = "SELECT name, email FROM users WHERE userId = ?";
        $stmt = $conn->prepare($userSql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        $result = $this->resultsData->getResultById($resultId);
        
        if (!$user || !$result) return false;
        
        $statusColors = [
            'approved' => 'success',
            'rejected' => 'danger'
        ];
        
        $statusIcons = [
            'approved' => '✅',
            'rejected' => '❌'
        ];
        
        $subject = $statusIcons[$status] . " Event Result " . ucfirst($status) . ": " . $result['resultTitle'];
        
        $recipientText = $recipientType === 'organizer' ? 
            "An event result for an event you organized has been " . $status . ":" :
            "Your event result submission has been " . $status . ":";
        
        $body = "
        <h2>Event Result " . ucfirst($status) . "</h2>
        <p>Dear " . htmlspecialchars($user['name']) . ",</p>
        <p>" . $recipientText . "</p>
        
        <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
            <h3>" . htmlspecialchars($result['resultTitle']) . "</h3>
            <p><strong>Event:</strong> " . htmlspecialchars($result['eventName']) . "</p>
            " . ($recipientType === 'organizer' ? "<p><strong>Submitted by:</strong> " . htmlspecialchars($result['addedByName']) . "</p>" : "") . "
            <p><strong>Date:</strong> " . date('F j, Y', strtotime($result['resultDate'])) . "</p>
            " . ($notes ? "<p><strong>Admin Notes:</strong> " . nl2br(htmlspecialchars($notes)) . "</p>" : "") . "
        </div>
        
        <p>" . ($status == 'approved' ? 
            "✅ The result is now visible on the website. " . ($recipientType === 'organizer' ? "Thank you for organizing this event!" : "Thank you for sharing the outcomes!") : 
            "❌ Please review the notes above.") . "</p>
        
        <p>Best regards,<br>Unity Volunteers Trust</p>
        ";
        
        return $this->sendEmail($user['email'], $subject, $body);
    }
}

// Create a global instance for backward compatibility
$resultsLogic = new ResultsLogic();
?>