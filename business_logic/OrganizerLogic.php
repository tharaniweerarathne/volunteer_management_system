<?php
// OrganizerLogic.php --> business_logic folder

require_once __DIR__ . "/../data_access/OrganizerData.php";
require_once __DIR__ . "/../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class OrganizerLogic {
    private $organizerData;
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->organizerData = new OrganizerData($conn);
    }
    
    // Send email using PHPMailer (similar to RegistrationLogic)
    private function sendEmail($toEmail, $toName, $subject, $body) {
        $mail = new PHPMailer(true);
        
        try {
            // SMTP Configuration - using same settings as RegistrationLogic
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'infocontact256@gmail.com'; 
            $mail->Password   = 'ffvr keeu ztxj bwpa'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // Sender and recipient
            $mail->setFrom('infocontact256@gmail.com', 'Unity Volunteers Trust');
            $mail->addAddress($toEmail, $toName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    // Get user email and name by ID
    private function getUserInfoById($userId) {
        $stmt = $this->conn->prepare("SELECT email, name FROM users WHERE userId = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        return $user ? $user : null;
    }
    
    // Send approval email to volunteer
    private function sendApprovalEmail($userId, $reviewerName = '', $reviewNotes = '') {
        $userInfo = $this->getUserInfoById($userId);
        if (!$userInfo) {
            return false;
        }
        
        $subject = "Congratulations! Your Organizer Request Has Been Approved";
        
        $body = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Hello {$userInfo['name']},</h2>
                <p>We are pleased to inform you that your request to become an Organizer has been <strong>APPROVED</strong>!</p>
                <p>You can now access organizer features including:</p>
                <ul>
                    <li>Create and manage events</li>
                    <li>Recruit volunteers for your events</li>
                    <li>Track event participation</li>
                    <li>Generate event reports</li>
                </ul>
                
                " . (!empty($reviewNotes) ? "
                <div style='background: #f0f0f0; padding: 10px; margin: 15px 0;'>
                    <p><strong>Reviewer Notes:</strong><br>{$reviewNotes}</p>
                </div>
                " : '') . "
                
                <p><strong>Reviewed by:</strong> " . htmlspecialchars($reviewerName ?: 'Admin Team') . "</p>
                <p><strong>Approval Date:</strong> " . date('F j, Y') . "</p>
                
                <p>Please login with your registered email and password to access Organizer features.</p>
                
                <p>Best regards,<br>
                <strong>The Unity Volunteers Trust Team</strong></p>
            </body>
            </html>
        ";
        
        return $this->sendEmail($userInfo['email'], $userInfo['name'], $subject, $body);
    }
    
    // Send rejection email to volunteer
    private function sendRejectionEmail($userId, $reviewerName = '', $reviewNotes = '') {
        $userInfo = $this->getUserInfoById($userId);
        if (!$userInfo) {
            return false;
        }
        
        $subject = "Organizer Request Status Update";
        
        $body = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Dear {$userInfo['name']},</h2>
                <p>Thank you for your interest in becoming an Organizer with Unity Volunteers Trust.</p>
                <p>After careful review, we regret to inform you that your organizer request has not been approved at this time.</p>
                
                " . (!empty($reviewNotes) ? "
                <div style='background: #f8d7da; padding: 10px; margin: 15px 0; border-left: 4px solid #dc3545;'>
                    <p><strong>Feedback from Review Team:</strong><br>{$reviewNotes}</p>
                </div>
                " : '') . "
                
                <p><strong>Reviewed by:</strong> " . htmlspecialchars($reviewerName ?: 'Admin Team') . "</p>
                <p><strong>Review Date:</strong> " . date('F j, Y') . "</p>
                
                <p>We encourage you to:</p>
                <ul>
                    <li>Continue volunteering in existing events</li>
                    <li>Gain more experience in event participation</li>
                    <li>You may reapply after 3 months</li>
                </ul>
                
                <p>If you have any questions about this decision, please contact our support team.</p>
                
                <p>Best regards,<br>
                <strong>The Unity Volunteers Trust Team</strong></p>
            </body>
            </html>
        ";
        
        return $this->sendEmail($userInfo['email'], $userInfo['name'], $subject, $body);
    }
    
    // Send notification to admin about new request
    public function sendNewRequestNotificationToAdmin($requestId, $applicantName, $organizationName = '') {
        // Get admin emails
        $stmt = $this->conn->prepare("SELECT email, name FROM users WHERE role = 'Admin'");
        $stmt->execute();
        $result = $stmt->get_result();
        $admins = $result->fetch_all(MYSQLI_ASSOC);
        
        if (empty($admins)) {
            return false;
        }
        
        $subject = "📋 New Organizer Request Received";
        
        foreach ($admins as $admin) {
            $body = "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <h2>Dear {$admin['name']},</h2>
                    <p>A new organizer request has been submitted and requires your review.</p>
                    
                    <div style='background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px;'>
                        <h3>Request Details:</h3>
                        <p><strong>Applicant:</strong> {$applicantName}</p>
                        <p><strong>Organization:</strong> " . htmlspecialchars($organizationName ?: 'Individual') . "</p>
                        <p><strong>Request ID:</strong> #{$requestId}</p>
                        <p><strong>Submission Time:</strong> " . date('Y-m-d H:i:s') . "</p>
                    </div>
                    
                    <p style='color: #dc3545; font-weight: bold;'>Action Required: Please review this request within 48 hours.</p>
                    
                    <p><a href='" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/organizer_requests.php' style='display: inline-block; padding: 10px 20px; background: #4F46E5; color: white; text-decoration: none; border-radius: 5px;'>Review Request Now</a></p>
                    
                    <p>Best regards,<br>
                    <strong>Unity Volunteers Trust System</strong></p>
                </body>
                </html>
            ";
            
            $this->sendEmail($admin['email'], $admin['name'], $subject, $body);
        }
        
        return true;
    }
    
    // Submit organizer application
    public function submitOrganizerApplication($userId, $organizationName, $organizationType, $organizationDescription, $yearsOfExperience, $previousEvents, $motivation) {
        // Check if user already has a pending or approved request
        $existingRequest = $this->organizerData->hasExistingRequest($userId);
        
        if ($existingRequest) {
            if ($existingRequest['requestStatus'] === 'Pending') {
                return ["success" => false, "message" => "You already have a pending organizer request."];
            } else if ($existingRequest['requestStatus'] === 'Approved') {
                return ["success" => false, "message" => "You are already an organizer."];
            }
        }
        
        // Validate input
        if (empty($motivation)) {
            return ["success" => false, "message" => "Please provide your motivation for becoming an organizer."];
        }
        
        // Submit request
        $requestId = $this->organizerData->submitOrganizerRequest(
            $userId,
            $organizationName,
            $organizationType,
            $organizationDescription,
            $yearsOfExperience,
            $previousEvents,
            $motivation
        );
        
        if (!$requestId) {
            return ["success" => false, "message" => "Failed to submit organizer request. Please try again."];
        }
        
        // Send notification to admin
        $userInfo = $this->getUserInfoById($userId);
        if ($userInfo) {
            $this->sendNewRequestNotificationToAdmin($requestId, $userInfo['name'], $organizationName);
        }
        
        return ["success" => true, "message" => "Your organizer request has been submitted successfully! Our admin team will review it soon.", "requestId" => $requestId];
    }
    
    // Get all organizer requests (for admin)
    public function getAllOrganizerRequests($status = null) {
        return $this->organizerData->getAllOrganizerRequests($status);
    }
    
    // Get organizer request by ID
    public function getOrganizerRequestById($requestId) {
        $request = $this->organizerData->getOrganizerRequestById($requestId);
        if (!$request) {
            return ["success" => false, "message" => "Request not found"];
        }
        return ["success" => true, "data" => $request];
    }
    
    // Get user's organizer requests
    public function getUserOrganizerRequests($userId) {
        return $this->organizerData->getUserOrganizerRequests($userId);
    }
    
    // Approve organizer request
    public function approveOrganizerRequest($requestId, $reviewerId, $reviewNotes = '') {
        if (!$this->organizerData->approveOrganizerRequest($requestId, $reviewerId, $reviewNotes)) {
            return ["success" => false, "message" => "Failed to approve request."];
        }
        
        // Get reviewer name
        $reviewerInfo = $this->getUserInfoById($reviewerId);
        $reviewerName = $reviewerInfo ? $reviewerInfo['name'] : '';
        
        // Get user ID from request
        $request = $this->organizerData->getOrganizerRequestById($requestId);
        if ($request) {
            // Send approval email to the volunteer
            $emailSent = $this->sendApprovalEmail($request['userId'], $reviewerName, $reviewNotes);
            
            if (!$emailSent) {
                error_log("Approval email failed to send for request ID: $requestId");
                // Still return success but with modified message
                return ["success" => true, "message" => "Organizer request approved! User role updated, but email notification failed to send."];
            }
        }
        
        return ["success" => true, "message" => "Organizer request approved successfully! User role has been updated and notification email sent."];
    }
    
    // Reject organizer request
    public function rejectOrganizerRequest($requestId, $reviewerId, $reviewNotes = '') {
        if (empty($reviewNotes)) {
            return ["success" => false, "message" => "Please provide a reason for rejection."];
        }
        
        if (!$this->organizerData->rejectOrganizerRequest($requestId, $reviewerId, $reviewNotes)) {
            return ["success" => false, "message" => "Failed to reject request."];
        }
        
        // Get reviewer name
        $reviewerInfo = $this->getUserInfoById($reviewerId);
        $reviewerName = $reviewerInfo ? $reviewerInfo['name'] : '';
        
        // Get user ID from request
        $request = $this->organizerData->getOrganizerRequestById($requestId);
        if ($request) {
            // Send rejection email to the volunteer
            $emailSent = $this->sendRejectionEmail($request['userId'], $reviewerName, $reviewNotes);
            
            if (!$emailSent) {
                error_log("Rejection email failed to send for request ID: $requestId");
                // Still return success but with modified message
                return ["success" => true, "message" => "Organizer request rejected, but email notification failed to send."];
            }
        }
        
        return ["success" => true, "message" => "Organizer request has been rejected and notification email sent."];
    }
    
    // Get all organizers
    public function getAllOrganizers() {
        return $this->organizerData->getAllOrganizers();
    }
    
    // Get organizer details
    public function getOrganizerDetails($userId) {
        $organizer = $this->organizerData->getOrganizerDetails($userId);
        if (!$organizer) {
            return ["success" => false, "message" => "Organizer not found"];
        }
        return ["success" => true, "data" => $organizer];
    }
    
    // Get request statistics
    public function getRequestStatistics() {
        return $this->organizerData->getRequestStatistics();
    }
    
    // Check if user can apply
    public function canUserApply($userId, $userRole) {
        // Only volunteers can apply
        if ($userRole !== 'Volunteer') {
            return ["canApply" => false, "message" => "Only volunteers can apply to become organizers."];
        }
        
        // Check for existing requests
        $existingRequest = $this->organizerData->hasExistingRequest($userId);
        
        if ($existingRequest) {
            if ($existingRequest['requestStatus'] === 'Pending') {
                return ["canApply" => false, "message" => "You have a pending request.", "hasRequest" => true, "status" => "Pending"];
            } else if ($existingRequest['requestStatus'] === 'Approved') {
                return ["canApply" => false, "message" => "You are already an organizer.", "hasRequest" => true, "status" => "Approved"];
            }
        }
        
        return ["canApply" => true, "message" => "You can apply to become an organizer."];
    }
    
    // Delete organizer request
    public function deleteOrganizerRequest($requestId) {
        if (!$this->organizerData->deleteOrganizerRequest($requestId)) {
            return ["success" => false, "message" => "Failed to delete request."];
        }
        return ["success" => true, "message" => "Request deleted successfully."];
    }
}
?>