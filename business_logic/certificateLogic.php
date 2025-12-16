<?php
require_once '../data_access/certificateData.php';
require_once '../data_access/attendanceData.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



class CertificateLogic {
    private $certificateData;
    private $attendanceData;
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->certificateData = new CertificateData();
        $this->attendanceData = new AttendanceData();
        $this->conn = $conn;
    }
    
    // Check if user is admin
    public function isAdmin($userId = null) {
        if (!$userId && isset($_SESSION['userId'])) {
            $userId = $_SESSION['userId'];
        }
        
        if (!$userId || !isset($_SESSION['role'])) {
            return false;
        }
        
        return $_SESSION['role'] === 'Admin';
    }
    
    // Get eligible volunteers for certificate
// Get eligible volunteers for certificate WITH SEARCH
public function getEligibleVolunteers($eventId, $search = '') {
    if (!$this->isAdmin()) {
        return ['success' => false, 'message' => 'Access denied'];
    }
    
    try {
        $volunteers = $this->certificateData->getEligibleVolunteers($eventId, $search);
        $event = $this->getEventDetails($eventId);
        
        // Get total count without search for comparison
        $totalCount = $this->certificateData->getTotalEligibleVolunteers($eventId);
        
        return [
            'success' => true,
            'volunteers' => $volunteers,
            'event' => $event,
            'count' => count($volunteers),
            'totalCount' => $totalCount,
            'searchTerm' => $search
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error fetching volunteers: ' . $e->getMessage()
        ];
    }
}
    
    // Get event details
    private function getEventDetails($eventId) {
        $sql = "SELECT e.*, s.skillName 
                FROM events e
                LEFT JOIN skills s ON e.requiredSkillId = s.skillId
                WHERE e.eventId = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Issue certificate
    public function issueCertificate($eventId, $userId) {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Access denied'];
        }
        
        // Check if already issued
        if ($this->certificateData->isCertificateIssued($eventId, $userId)) {
            return ['success' => false, 'message' => 'Certificate already issued'];
        }
        
        // Check if volunteer is eligible (present in attendance)
        $checkSql = "SELECT status FROM attendance 
                    WHERE eventId = ? AND userId = ? AND status = 'Present'";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $eventId, $userId);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows === 0) {
            return ['success' => false, 'message' => 'Volunteer not eligible for certificate'];
        }
        
        try {
            // Generate certificate
            $result = $this->generateCertificatePDF($eventId, $userId);
            
            if (!$result['success']) {
                return $result;
            }
            
            // Save certificate record
            $certificateId = $this->certificateData->issueCertificate(
                $eventId,
                $userId,
                $result['certificateNumber'],
                $result['filePath'],
                $_SESSION['userId']
            );
            
            if ($certificateId) {
                // Send notifications using your MessageData
                $this->sendCertificateNotifications($userId, $certificateId);
                
                return [
                    'success' => true,
                    'certificateId' => $certificateId,
                    'certificateNumber' => $result['certificateNumber'],
                    'filePath' => $result['filePath'],
                    'message' => 'Certificate issued successfully'
                ];
            } else {
                // Clean up generated file if database insert failed
                if (file_exists('../' . $result['filePath'])) {
                    unlink('../' . $result['filePath']);
                }
                
                return ['success' => false, 'message' => 'Failed to save certificate record'];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error issuing certificate: ' . $e->getMessage()
            ];
        }
    }
    
    // Generate certificate PDF
    private function generateCertificatePDF($eventId, $userId) {
        // Get volunteer and event details
        $sql = "SELECT u.name as volunteerName, u.email, 
                       e.eventName, e.category, e.startDate,
                       s.skillName
                FROM users u
                JOIN attendance a ON u.userId = a.userId
                JOIN events e ON a.eventId = e.eventId
                LEFT JOIN skills s ON e.requiredSkillId = s.skillId
                WHERE e.eventId = ? AND u.userId = ? AND a.status = 'Present'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        
        if (!$data) {
            return ['success' => false, 'message' => 'Volunteer or event not found'];
        }
        
        // Generate certificate number
        $certificateNumber = $this->certificateData->generateCertificateNumber($eventId, $userId);
        
        // Generate HTML for PDF
        $html = $this->generateCertificateHTML($data, $certificateNumber);
        
        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        // Create certificates directory if not exists
        $directory = '../assets/certificates/';
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        
        // Save PDF file
        $filename = 'certificate_' . $certificateNumber . '.pdf';
        $filePath = 'assets/certificates/' . $filename;
        $fullPath = $directory . $filename;
        
        file_put_contents($fullPath, $dompdf->output());
        
        return [
            'success' => true,
            'certificateNumber' => $certificateNumber,
            'filePath' => $filePath
        ];
    }
    
    // Generate certificate HTML
    private function generateCertificateHTML($data, $certificateNumber) {
        $issueDate = date('F j, Y');
        $eventDate = date('F j, Y', strtotime($data['startDate']));
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
                .certificate { 
                    width: 100%; 
                    height: 100vh; 
                    border: 20px solid #2c3e50;
                    padding: 50px;
                    background: #f9f9f9;
                    position: relative;
                    text-align: center;
                }
                .header { 
                    color: #2c3e50; 
                    font-size: 48px; 
                    margin-bottom: 20px;
                    font-weight: bold;
                }
                .subheader { 
                    color: #7f8c8d; 
                    font-size: 24px; 
                    margin-bottom: 40px;
                }
                .title { 
                    font-size: 36px; 
                    color: #2980b9; 
                    margin: 30px 0;
                    font-weight: bold;
                }
                .name { 
                    font-size: 42px; 
                    color: #2c3e50; 
                    margin: 40px 0;
                    padding: 20px;
                    border-bottom: 3px solid #3498db;
                    display: inline-block;
                }
                .details { 
                    font-size: 20px; 
                    color: #555; 
                    margin: 20px 0;
                    line-height: 1.8;
                }
                .footer { 
                    margin-top: 60px; 
                    color: #7f8c8d; 
                    font-size: 16px;
                }
                .signature { 
                    margin-top: 40px; 
                    border-top: 2px solid #000;
                    display: inline-block;
                    padding-top: 10px;
                    width: 300px;
                }
                .cert-number {
                    position: absolute;
                    bottom: 20px;
                    right: 20px;
                    font-size: 12px;
                    color: #95a5a6;
                }
            </style>
        </head>
        <body>
            <div class="certificate">
                <div class="header">Unity Volunteers Trust</div>
                <div class="subheader">Certificate of Appreciation</div>
                <div class="title">This Certificate is Proudly Presented to</div>
                <div class="name">' . htmlspecialchars($data['volunteerName']) . '</div>
                <div class="details">
                    In recognition of valuable contribution as a volunteer for<br>
                    <strong>' . htmlspecialchars($data['eventName']) . '</strong><br>
                    <em>' . htmlspecialchars($data['skillName'] . ' - ' . $data['category']) . '</em><br>
                    Held on ' . $eventDate . '<br>
                    In appreciation of dedicated service and commitment
                </div>
                <div class="footer">
                    <div class="signature">
                        <strong>Unity Volunteers Trust</strong><br>
                        Director
                    </div>
                    <div style="margin-top: 20px;">
                        Date Issued: ' . $issueDate . '
                    </div>
                </div>
                <div class="cert-number">
                    Certificate No: ' . $certificateNumber . '
                </div>
            </div>
        </body>
        </html>';
    }
    
    // Send certificate notifications using your MessageData
    private function sendCertificateNotifications($userId, $certificateId) {
        // Get certificate details
        $certificate = $this->certificateData->getCertificate($certificateId);
        
        if (!$certificate) return;
        
        // Load your MessageData
        require_once '../data_access/MessageData.php';
        $messageData = new MessageData($this->conn);
        
        // Send internal message
        $messageData->sendMessage(
            $_SESSION['userId'], // Admin as sender
            $userId,
            'Certificate Issued - ' . $certificate['certificateNumber'],
            "A certificate has been issued for your participation in '{$certificate['eventName']}'. Certificate Number: {$certificate['certificateNumber']}"
        );
        
        // Send email notification
        $this->sendCertificateEmail($userId, $certificate);
    }
    
    // Send certificate email
    private function sendCertificateEmail($userId, $certificate) {
        try {
            // Get user email
            $userSql = "SELECT email FROM users WHERE userId = ?";
            $userStmt = $this->conn->prepare($userSql);
            $userStmt->bind_param("i", $userId);
            $userStmt->execute();
            $user = $userStmt->get_result()->fetch_assoc();
            
            if (!$user) return false;
            
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
            $mail->addAddress($user['email']);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = "Certificate Issued: " . $certificate['eventName'];
            
            $body = "
            <h2>Certificate of Appreciation</h2>
            <p>Dear Volunteer,</p>
            <p>We are pleased to inform you that a certificate has been issued for your participation in:</p>
            <h3>" . htmlspecialchars($certificate['eventName']) . "</h3>
            <p><strong>Certificate Number:</strong> {$certificate['certificateNumber']}</p>
            <p><strong>Event Date:</strong> " . date('F j, Y', strtotime($certificate['startDate'])) . "</p>
            <p><strong>Skill/Category:</strong> " . htmlspecialchars($certificate['skillName'] . ' - ' . $certificate['category']) . "</p>
            <p><strong>Issued On:</strong> " . date('F j, Y', strtotime($certificate['issueDate'])) . "</p>
            <p>Your certificate is available for download from your account dashboard.</p>
            <p>Thank you for your valuable contribution!</p>
            <p>Best regards,<br>Unity Volunteers Trust</p>
            ";
            
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);
            
            return $mail->send();
            
        } catch (Exception $e) {
            error_log("Certificate email error: " . $e->getMessage());
            return false;
        }
    }
    
    // Bulk issue certificates for all eligible volunteers in an event
    public function bulkIssueCertificates($eventId) {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Access denied'];
        }
        
        $eligibleVolunteers = $this->certificateData->getEligibleVolunteers($eventId);
        
        if (empty($eligibleVolunteers)) {
            return ['success' => false, 'message' => 'No eligible volunteers found'];
        }
        
        $results = [
            'total' => count($eligibleVolunteers),
            'success' => 0,
            'failed' => 0,
            'certificates' => []
        ];
        
        foreach ($eligibleVolunteers as $volunteer) {
            $result = $this->issueCertificate($eventId, $volunteer['userId']);
            
            if ($result['success']) {
                $results['success']++;
                $results['certificates'][] = [
                    'volunteerName' => $volunteer['name'],
                    'certificateNumber' => $result['certificateNumber']
                ];
            } else {
                $results['failed']++;
            }
        }
        
        return [
            'success' => true,
            'results' => $results,
            'message' => "Issued {$results['success']} certificates successfully, {$results['failed']} failed"
        ];
    }
    
    // Get certificate statistics
    public function getCertificateStats() {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Access denied'];
        }
        
        $sql = "SELECT 
                COUNT(*) as totalCertificates,
                COUNT(DISTINCT eventId) as totalEvents,
                COUNT(DISTINCT userId) as totalVolunteers,
                DATE(issuedAt) as issueDate,
                COUNT(*) as dailyCount
                FROM certificates
                GROUP BY DATE(issuedAt)
                ORDER BY issueDate DESC
                LIMIT 30";
        
        $result = $this->conn->query($sql);
        $stats = $result->fetch_all(MYSQLI_ASSOC);
        
        return [
            'success' => true,
            'stats' => $stats
        ];
    }
}
?>