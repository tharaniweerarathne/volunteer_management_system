<?php
require_once __DIR__ . '/../data_access/ContactMessageData.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ContactMessageLogic {
    private $conn;
    private $contactMessageData;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->contactMessageData = new ContactMessageData($conn);
    }

    // Submit contact form
    public function submitContactMessage($senderName, $senderEmail, $message) {
        // Validation
        if (empty($senderName) || empty($senderEmail) || empty($message)) {
            return ["success" => false, "message" => "All fields are required."];
        }

        if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
            return ["success" => false, "message" => "Invalid email address."];
        }

        // Create message
        $messageId = $this->contactMessageData->createMessage($senderName, $senderEmail, $message);
        
        if ($messageId) {
            return ["success" => true, "message" => "Your message has been sent successfully! We'll get back to you soon.", "messageId" => $messageId];
        }
        
        return ["success" => false, "message" => "Failed to send message. Please try again."];
    }

    // Get all messages
    public function getAllMessages() {
        return $this->contactMessageData->getAllMessages();
    }

    // Get single message
    public function getMessageById($messageId) {
        return $this->contactMessageData->getMessageById($messageId);
    }

    // Send reply via email
    public function sendReply($messageId, $repliedBy, $replyMessage) {
        // Get original message details
        $originalMessage = $this->contactMessageData->getMessageById($messageId);
        
        if (!$originalMessage) {
            return ["success" => false, "message" => "Message not found."];
        }

        if ($originalMessage['status'] === 'replied') {
            return ["success" => false, "message" => "This message has already been replied to."];
        }

        // Send email
        $emailResult = $this->sendReplyEmail(
            $originalMessage['senderEmail'],
            $originalMessage['senderName'],
            $originalMessage['message'],
            $replyMessage
        );

        if ($emailResult['success']) {
            // Update database
            if ($this->contactMessageData->replyToMessage($messageId, $repliedBy, $replyMessage)) {
                return ["success" => true, "message" => "Reply sent successfully!"];
            } else {
                return ["success" => false, "message" => "Email sent but failed to update database."];
            }
        }

        return $emailResult;
    }

    // Send reply email using PHPMailer
    private function sendReplyEmail($recipientEmail, $recipientName, $originalMessage, $replyMessage) {
        $mail = new PHPMailer(true);

        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'infocontact256@gmail.com';
            $mail->Password = 'ffvr keeu ztxj bwpa';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email settings
            $mail->setFrom('infocontact256@gmail.com', 'Unity Volunteers Trust');
            $mail->addAddress($recipientEmail, $recipientName);
            $mail->addReplyTo('infocontact256@gmail.com', 'Unity Volunteers Trust');

            $mail->isHTML(true);
            $mail->Subject = 'Re: Your Message to Unity Volunteers Trust';
            
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background: linear-gradient(135deg, #ff6200 0%, #994524 100%); padding: 30px; text-align: center;'>
                        <h2 style='color: white; margin: 0;'>Unity Volunteers Trust</h2>
                    </div>
                    
                    <div style='padding: 30px; background-color: #f9fafb;'>
                        <p style='font-size: 16px; color: #374151;'>Dear $recipientName,</p>
                        
                        <p style='font-size: 14px; color: #6b7280;'>Thank you for reaching out to Unity Volunteers Trust. We have reviewed your message and here's our response:</p>
                        
                        <div style='background-color: white; padding: 20px; border-left: 4px solid #ff6200; margin: 20px 0;'>
                            <p style='font-size: 14px; color: #111317; line-height: 1.6; margin: 0;'>" . nl2br(htmlspecialchars($replyMessage)) . "</p>
                        </div>
                        
                        <div style='background-color: #e5e7eb; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                            <p style='font-size: 12px; color: #6b7280; margin: 0 0 8px 0;'><strong>Your Original Message:</strong></p>
                            <p style='font-size: 13px; color: #374151; margin: 0; line-height: 1.5;'>" . nl2br(htmlspecialchars($originalMessage)) . "</p>
                        </div>
                        
                        <p style='font-size: 14px; color: #6b7280;'>If you have any further questions, please don't hesitate to reach out to us.</p>
                        
                        <p style='font-size: 14px; color: #374151; margin-top: 25px;'>
                            Best regards,<br>
                            <strong style='color: #ff6200;'>Unity Volunteers Trust Team</strong>
                        </p>
                    </div>
                    
                    <div style='background-color: #111317; padding: 20px; text-align: center;'>
                        <p style='font-size: 12px; color: #d1d5db; margin: 0;'>© 2024 Unity Volunteers Trust. All rights reserved.</p>
                    </div>
                </div>
            ";

            $mail->send();
            return ["success" => true, "message" => "Reply sent successfully"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Failed to send reply. Error: {$mail->ErrorInfo}"];
        }
    }

    // Get pending messages count
    public function getPendingCount() {
        return $this->contactMessageData->getMessageCountByStatus('pending');
    }
}
?>